<?php

declare(strict_types=1);

/*
 * This file is part of the Esteren Maps package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com> and Studio Agate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EsterenMaps\Services;

use Doctrine\ORM\EntityManagerInterface;
use EsterenMaps\Api\MapApi;
use EsterenMaps\Entity\Map;
use EsterenMaps\Entity\Marker;
use EsterenMaps\Entity\TransportModifier;
use EsterenMaps\Entity\TransportType;
use Twig\Environment;

/**
 * Uses Dijkstra algorithm to calculate a path between two markers.
 */
class DirectionsManager
{
    private $entityManager;
    private $twig;
    private $mapApi;

    public function __construct(
        MapApi $mapApi,
        EntityManagerInterface $entityManager,
        Environment $twig
    ) {
        $this->entityManager = $entityManager;
        $this->twig = $twig;
        $this->mapApi = $mapApi;
    }

    public function getDirections(Map $map, Marker $start, Marker $end, int $hoursPerDay = 7, TransportType $transportType = null): array
    {
        $directions = $this->doGetDirections($map, $start, $end, $hoursPerDay, $transportType);

        $directions['from_cache'] = false;

        return $directions;
    }

    private function doGetDirections(Map $map, Marker $start, Marker $end, int $hoursPerDay, TransportType $transportType = null): array
    {
        $data = $this->mapApi->getMap($map->getId());

        // For performances & memory, let's remove this key, it's useless here.
        unset($data['templates']);

        $nodes = [];
        $edges = [];

        // Reformat nodes and edges for a better use of the Dijkstra algorithm.
        // We here have a list of "start" and "end" markers, and routes.
        // We need nodes (markers) and edges (routes).
        foreach ($data['map']['routes'] as $routeId => $route) {
            $markerStartId = $route['marker_start'];
            $markerEndId = $route['marker_end'];

            // Create an edge based on a route.
            $edge = [
                'id' => $routeId,
                'distance' => $route['distance'],
                'type' => $route['route_type'],
                'start' => $markerStartId,
                'end' => $markerEndId,
            ];

            // Add nodes and edge.
            if ($markerStartId) {
                // Set the start node if does not exist.
                if (!\array_key_exists($markerStartId, $nodes)) {
                    $nodes[$markerStartId] = [
                        'id' => $markerStartId,
                        'name' => $data['map']['markers'][$markerStartId]['name'],
                        'neighbours' => [],
                    ];
                }

                $nodes[$markerStartId]['neighbours'][$routeId] = [
                    'distance' => $route['distance'],
                    'end' => $route['marker_end'],
                ];
            }
            if ($markerEndId) {
                // Set the end node if does not exist.
                if (!\array_key_exists($markerEndId, $nodes)) {
                    $nodes[$markerEndId] = [
                        'id' => $markerEndId,
                        'name' => $data['map']['markers'][$markerEndId]['name'],
                        'neighbours' => [],
                    ];
                }

                $nodes[$markerEndId]['neighbours'][$routeId] = [
                    'distance' => $route['distance'],
                    'end' => $markerStartId,
                ];
            }
            $edges[$routeId] = $edge;
        }

        $edges = $this->filterEdges($edges, $transportType);
        $nodes = $this->filterNodes($nodes, $edges);

        $paths = $this->dijkstra($nodes, $edges, $start->getId(), $end->getId());

        $routesIds = \array_values($paths);
        $markersArray = \array_filter($data['map']['markers'], function ($marker) use ($paths) {
            return \array_key_exists($marker['id'], $paths);
        });
        $routesArray = \array_filter($data['map']['routes'], function ($route) use ($routesIds) {
            return \in_array($route['id'], $routesIds, true);
        });

        $steps = [];

        // Remove unused fields
        foreach ($paths as $markerId => $routeId) {
            $marker = $markersArray[$markerId];
            $marker['route'] = $routeId ? $routesArray[$routeId] : null;
            $marker['marker_type'] = $marker['marker_type'] ? $data['references']['markers_types'][$marker['marker_type']] : null;
            $marker['faction'] = $marker['faction'] ? $data['references']['factions'][$marker['faction']] : null;
            if (isset($marker['route'])) {
                $marker['route']['faction'] = $marker['route']['faction'] ? $data['references']['factions'][$marker['route']['faction']] : null;
                $marker['route']['route_type'] = $marker['route']['route_type'] ? $data['references']['routes_types'][$marker['route']['route_type']] : null;
            }
            unset(
                $marker['route']['marker_start'],
                $marker['route']['marker_end']
            );
            $steps[] = $marker;
        }

        $data = $this->getDataArray($start, $end, $steps, $hoursPerDay, $transportType);

        if (!$transportType) {
            $data['path_view'] = $this->twig->render('esteren_maps/Api/path_view.html.twig', $data);

            return $data;
        }

        $data = $this->updateTravelDuration($data, $routesArray, $hoursPerDay, $transportType);

        $data['path_view'] = $this->twig->render('esteren_maps/Api/path_view.html.twig', $data);

        return $data;
    }

    /**
     * Applies Dijkstra algorithm to calculate minimal distance between source and target.
     *
     * Implementation of http://codereview.stackexchange.com/questions/75641/dijkstras-algorithm-in-php
     *
     * @see http://codereview.stackexchange.com/questions/75641/dijkstras-algorithm-in-php
     *
     * Return an array where keys are the markers IDs and the values are route IDs.
     */
    private function dijkstra(array $nodes, array $edges, int $start, int $end): array
    {
        /** @var array[][] $distances */
        $distances = [];

        foreach ($nodes as $id => $node) {
            foreach ($node['neighbours'] as $nid => $neighbour) {
                $distances[$id][$neighbour['end']] = [
                    'edge' => $edges[$nid],
                    'distance' => $neighbour['distance'],
                ];
            }
        }

        //initialize the array for storing
        $S = []; //the nearest path with its parent and weight
        $Q = []; //the left nodes without the nearest path

        foreach (\array_keys($distances) as $val) {
            $Q[$val] = \INF;
        }
        $Q[$start] = 0;

        //start calculating
        while (0 !== \count($Q)) {
            $min = \array_search(\min($Q), $Q, true); //the most min weight
            if ($min === $end) {
                break;
            }
            if (!\array_key_exists($min, $distances)) {
                // In the case the route ID does not exist, we set it as empty.
                // It can only happen if the transport selected "removes" some inaccessible routes,
                //  specificly when the route type has a speed modifier of 0 with this transport.
                $distances[$min] = [];
            }
            foreach ($distances[$min] as $key => $val) {
                $dist = $val['distance'];

                if (!empty($Q[$key]) && $Q[$min] + $dist < $Q[$key]) {
                    $Q[$key] = $Q[$min] + $dist;
                    $S[$key] = [$min, $Q[$key]];
                }
            }
            unset($Q[$min]);
        }

        if (!\array_key_exists($end, $S)) {
            return [];
        }

        $path = [];
        $pos = $end;
        while ($pos !== $start) {
            $path[] = $pos;
            $pos = $S[$pos][0];
        }
        $path[] = $start;
        $path = \array_reverse($path);

        $realPath = [];

        foreach ($path as $k => $nodeId) {
            $next = $path[$k + 1] ?? null;

            $realPath[$nodeId] = null;

            if ($next) {
                $dist = \INF;
                $realEdge = null;

                foreach ($nodes[$nodeId]['neighbours'] as $edgeId => $edge) {
                    if ($edge['distance'] < $dist && $edge['end'] === $next) {
                        $realEdge = $edges[$edgeId];
                        $dist = $edge['distance'];
                    }
                }

                if ($realEdge) {
                    $realPath[$nodeId] = $realEdge['id'];
                }
            }
        }

        return $realPath;
    }

    private function getDataArray(Marker $from, Marker $to, array $directions, int $hoursPerDay, TransportType $transport = null): array
    {
        $distance = null;
        $NE = [];
        $SW = [];

        foreach ($directions as $step) {
            if (!$step['route']) {
                continue;
            }

            $distance += $step['route']['distance'];

            if ($step['route']) {
                /** @var array $coords */
                $coords = $step['route']['coordinates'];

                // Evaluate bounds
                foreach ($coords as $latLng) {
                    if (!isset($NE['lat']) || ($NE['lat'] < $latLng['lat'])) {
                        $NE['lat'] = $latLng['lat'];
                    }
                    if (!isset($NE['lng']) || ($NE['lng'] < $latLng['lng'])) {
                        $NE['lng'] = $latLng['lng'];
                    }
                    if (!isset($SW['lat']) || ($SW['lat'] > $latLng['lat'])) {
                        $SW['lat'] = $latLng['lat'];
                    }
                    if (!isset($SW['lng']) || ($SW['lng'] > $latLng['lng'])) {
                        $SW['lng'] = $latLng['lng'];
                    }
                }
            }
        }

        return [
            'bounds' => ['northEast' => $NE, 'southWest' => $SW],
            'duration_raw' => null,
            'duration_real' => ['days' => null, 'hours' => null],
            'end' => $to->jsonSerialize(),
            'found' => \count($directions) > 0,
            'hours_per_day' => $hoursPerDay,
            'number_of_steps' => \count($directions) ? (\count($directions) - 2) : 0,
            'path' => $directions,
            'path_view' => null,
            'start' => $from->jsonSerialize(),
            'total_distance' => $distance,
            'transport' => $transport ? $transport->jsonSerialize() : null,
        ];
    }

    private function updateTravelDuration(array $data, array $routes, int $hoursPerDay, TransportType $transport): array
    {
        $total = 0;

        // Get (again) the necessary transport modifiers for the final route.
        $routesTypes = [];
        foreach ($routes as $route) {
            $routesTypes[$route['route_type']] = $route['route_type'];
        }

        $transportModifiersUnsorted = $this->entityManager->getRepository(TransportModifier::class)->findBy([
            'routeType' => $routesTypes,
            'transportType' => $transport,
        ]);

        /** @var TransportModifier[][] $transportModifiers */
        $transportModifiers = [];
        foreach ($transportModifiersUnsorted as $transportModifier) {
            $transportModifiers[$transportModifier->getRouteType()->getId()][] = $transportModifier;
        }

        foreach ($data['path'] as &$path) {
            $path['duration_raw'] = null;
            $path['duration_real'] = null;

            if (!isset($path['route']['distance'])) {
                continue;
            }

            $distance = $path['route']['distance'];
            $transportModifier = null;

            $routeTypeId = $path['route']['route_type']['id'] ?? null;

            if (null === $routeTypeId) {
                continue;
            }

            foreach ($transportModifiers[$routeTypeId] as $modifier) {
                if ($modifier->getTransportType()->getId() === $transport->getId()) {
                    $transportModifier = $modifier;

                    break;
                }
            }

            if ($transportModifier) {
                $percentage = (float) $transportModifier->getPercentage();
                $speed = $transport->getSpeed() * ($percentage / 100);
                $pathHours = $distance / $speed;
                $total += $pathHours;

                $realisticDays = $pathHours / $hoursPerDay;

                $days = (int) \floor($realisticDays);
                $hours = ($realisticDays - $days) * $hoursPerDay;
                $minutes = (int) \ceil(($hours - \floor($hours)) * 100 * 60 / 100);

                if (60 === $minutes) {
                    $hours++;
                    $minutes = 0;
                }

                $interval = new \DateInterval('PT'.((int) $hours).'H'.$minutes.'M');

                // Override $path by reference to add data to the final array
                $path['duration_raw'] = $interval->format('P%yY%mM%dDT%hH%iM0S');
                $path['duration_real'] = [
                    'days' => (int) $days,
                    'hours' => (int) \floor($hours),
                ];
            }
        }

        $hours = (int) \floor($total);
        $minutes = (int) \ceil(($total - $hours) * 100 * 60 / 100);

        $interval = new \DateInterval('PT'.$hours.'H'.$minutes.'M');
        $start = new \DateTime();
        $end = new \DateTime();
        $end->add($interval);

        // Recreating the interval allows automatic calculation of days/months.
        $interval = $start->diff($end);

        // Here we'll try to convert hours into a more "realistic" travel time.
        $realisticDays = $total / $hoursPerDay;

        $days = (int) \floor($realisticDays);
        $hours = (int) \floor(($realisticDays - $days) * $hoursPerDay);

        $data['duration_raw'] = $interval->format('P%yY%mM%dDT%hH%iM0S');
        $data['duration_real'] = [
            'days' => $days,
            'hours' => $hours,
        ];

        return $data;
    }

    /**
     * Filter routes that are incompatible with this transport type.
     */
    private function filterEdges(array $edges, TransportType $transportType = null): array
    {
        if (!$transportType) {
            return $edges;
        }

        $routesTypes = [];

        foreach ($edges as $route) {
            $routesTypes[$route['type']] = $route['type'];
        }

        $transportModifiers = $this->entityManager->getRepository(TransportModifier::class)->findBy([
            'routeType' => $routesTypes,
            'transportType' => $transportType,
        ]);

        $routesTypesToDelete = [];

        // Check that the transport have a good value here
        foreach ($transportModifiers as $modifier) {
            if ($modifier->getPercentage() <= 0.00001) {
                $id = $modifier->getRouteType()->getId();
                $routesTypesToDelete[$id] = $id;
            }
        }

        foreach ($edges as $k => $edge) {
            if (\array_key_exists($edge['type'], $routesTypesToDelete)) {
                unset($edges[$k]);
            }
        }

        return $edges;
    }

    /**
     * Filter markers that are not reachable via filtered routes.
     */
    private function filterNodes(array $nodes, array $edges): array
    {
        // Filter markers from filtered routes
        foreach ($nodes as $k => $node) {
            // Remove potential neighbours that are not reachable because of incompatible routes.
            foreach ($node['neighbours'] as $edgeId => $n) {
                if (!\array_key_exists($edgeId, $edges)) {
                    unset($nodes[$k]['neighbours'][$edgeId]);
                }
            }
            // If a marker has no more neighbours, it can't be crossed, so remove it.
            if (!\count($node['neighbours'])) {
                unset($nodes[$k]);
            }
        }

        return $nodes;
    }
}
