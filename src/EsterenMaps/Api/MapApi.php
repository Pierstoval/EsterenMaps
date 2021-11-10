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

namespace EsterenMaps\Api;

use EsterenMaps\Entity\MarkerType;
use EsterenMaps\Form\ApiMarkersType;
use EsterenMaps\Form\ApiRouteType;
use EsterenMaps\Form\ApiZoneType;
use EsterenMaps\Repository\FactionsRepository;
use EsterenMaps\Repository\MapsRepository;
use EsterenMaps\Repository\MarkersRepository;
use EsterenMaps\Repository\MarkersTypesRepository;
use EsterenMaps\Repository\RoutesRepository;
use EsterenMaps\Repository\RoutesTypesRepository;
use EsterenMaps\Repository\TransportTypesRepository;
use EsterenMaps\Repository\ZonesRepository;
use EsterenMaps\Repository\ZonesTypesRepository;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Environment;

class MapApi
{
    private Environment $twig;
    private Packages $asset;
    private FormFactoryInterface $formFactory;
    private MapsRepository $mapsRepository;
    private MarkersRepository $markersRepository;
    private RoutesRepository $routesRepository;
    private ZonesRepository $zonesRepository;
    private MarkersTypesRepository $markersTypesRepository;
    private RoutesTypesRepository $routesTypesRepository;
    private ZonesTypesRepository $zonesTypesRepository;
    private FactionsRepository $factionsRepository;
    private TransportTypesRepository $transportTypesRepository;

    public function __construct(
        MapsRepository $mapsRepository,
        MarkersRepository $markersRepository,
        RoutesRepository $routesRepository,
        ZonesRepository $zonesRepository,
        MarkersTypesRepository $markersTypesRepository,
        RoutesTypesRepository $routesTypesRepository,
        ZonesTypesRepository $zonesTypesRepository,
        FactionsRepository $factionsRepository,
        TransportTypesRepository $transportTypesRepository,
        Environment $twig,
        Packages $asset,
        FormFactoryInterface $formFactory
    ) {
        $this->twig = $twig;
        $this->asset = $asset;
        $this->formFactory = $formFactory;
        $this->mapsRepository = $mapsRepository;
        $this->markersRepository = $markersRepository;
        $this->routesRepository = $routesRepository;
        $this->zonesRepository = $zonesRepository;
        $this->markersTypesRepository = $markersTypesRepository;
        $this->routesTypesRepository = $routesTypesRepository;
        $this->zonesTypesRepository = $zonesTypesRepository;
        $this->factionsRepository = $factionsRepository;
        $this->transportTypesRepository = $transportTypesRepository;
    }

    public function getMap($id): array
    {
        return $this->doGetMap($id);
    }

    private function doGetMap($id): array
    {
        $data = [
            'map' => $this->mapsRepository->findForApi($id),
            'references' => [
                'markers_types' => $this->markersTypesRepository->findForApi(),
                'routes_types' => $this->routesTypesRepository->findForApi(),
                'zones_types' => $this->zonesTypesRepository->findForApi(),
                'factions' => $this->factionsRepository->findForApi(),
                'transports' => $this->transportTypesRepository->findForApi(),
            ],
            'templates' => $this->getTemplates(),
        ];

        // Map info
        $data['map']['markers'] = $this->markersRepository->findForApiByMap($id);
        $data['map']['routes'] = $this->routesRepository->findForApiByMap($id);
        $data['map']['zones'] = $this->zonesRepository->findForApiByMap($id);

        return $this->filterMapData($data);
    }

    private function filterMapData(array $data): array
    {
        $data['map']['bounds'] = \json_decode($data['map']['bounds'], true);

        foreach ($data['map']['markers'] as &$marker) {
            $marker['latitude'] = (float) $marker['latitude'];
            $marker['longitude'] = (float) $marker['longitude'];
        }

        foreach ($data['map']['zones'] as &$zone) {
            $zone['coordinates'] = $this->filterCoordinates(\json_decode($zone['coordinates'], true));
        }

        foreach ($data['map']['routes'] as &$route) {
            $route['coordinates'] = $this->filterCoordinates(\json_decode($route['coordinates'], true));
            if ($route['forced_distance']) {
                $route['distance'] = $route['forced_distance'];
            }
            unset($route['forced_distance']);
        }

        foreach ($data['references']['markers_types'] as &$markerType) {
            $markerType['icon'] = $this->asset->getUrl(MarkerType::PUBLIC_PATH_BASE.$markerType['icon']);
        }

        return $data;
    }

    private function filterCoordinates(?array $coordinates): array
    {
        if (null === $coordinates) {
            return [];
        }

        foreach ($coordinates as $k => $coordinate) {
            if (\is_array($coordinate) && !isset($coordinate['lat'])) {
                foreach ($coordinate as $l => $coord) {
                    $coordinates[$k][$l]['lat'] = (float) $coord['lat'];
                    $coordinates[$k][$l]['lng'] = (float) $coord['lng'];
                }
            } else {
                $coordinates[$k]['lat'] = (float) $coordinate['lat'];
                $coordinates[$k]['lng'] = (float) $coordinate['lng'];
            }
        }

        return $coordinates;
    }

    private function getTemplates(): array
    {
        return [
            'LeafletPopupMarkerBaseContent' => $this->twig->render('esteren_maps/Api/popupContentMarker.html.twig'),
            'LeafletPopupPolylineBaseContent' => $this->twig->render('esteren_maps/Api/popupContentPolyline.html.twig'),
            'LeafletPopupPolygonBaseContent' => $this->twig->render('esteren_maps/Api/popupContentPolygon.html.twig'),
            'LeafletPopupMarkerEditContent' => $this->twig->render('esteren_maps/Api/popupContentMarkerEditMode.html.twig', [
                'form' => $this->formFactory->create(ApiMarkersType::class)->createView(),
            ]),
            'LeafletPopupPolylineEditContent' => $this->twig->render('esteren_maps/Api/popupContentPolylineEditMode.html.twig', [
                'form' => $this->formFactory->create(ApiRouteType::class)->createView(),
            ]),
            'LeafletPopupPolygonEditContent' => $this->twig->render('esteren_maps/Api/popupContentPolygonEditMode.html.twig', [
                'form' => $this->formFactory->create(ApiZoneType::class)->createView(),
            ]),
        ];
    }
}
