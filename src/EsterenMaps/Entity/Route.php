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

namespace EsterenMaps\Entity;

use Doctrine\ORM\Mapping as ORM;
use EsterenMaps\DTO\Api\MapElementDTOInterface;
use EsterenMaps\DTO\Api\RouteDTO;
use EsterenMaps\Model\LatLng;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="maps_routes")
 * @ORM\Entity(repositoryClass="EsterenMaps\Repository\RoutesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Route implements \JsonSerializable, MapElementInterface
{
    use CanBeUserNote;
    use TimestampableEntity;

    /**
     * If it's false, the object won't be refreshed by the listener.
     */
    public bool $refresh = true;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private string $name;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(name="coordinates", type="text")
     */
    private string $coordinates = '';

    /**
     * @ORM\Column(name="distance", type="float", precision=12, scale=6, options={"default" = 0})
     */
    private float $distance = 0;

    /**
     * @ORM\Column(name="forced_distance", type="float", precision=12, scale=6, nullable=true)
     */
    private ?float $forcedDistance = null;

    /**
     * @ORM\Column(name="guarded", type="boolean")
     */
    private bool $guarded = false;

    /**
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\Marker", inversedBy="routesStart")
     * @ORM\JoinColumn(name="marker_start_id", nullable=true)
     */
    private ?Marker $markerStart;

    /**
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\Marker", inversedBy="routesEnd")
     * @ORM\JoinColumn(name="marker_end_id", nullable=true)
     */
    private ?Marker $markerEnd;

    /**
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\Map")
     * @ORM\JoinColumn(name="map_id", nullable=false)
     */
    private Map $map;

    /**
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\Faction")
     * @ORM\JoinColumn(name="faction_id", nullable=true)
     */
    private ?Faction $faction;

    /**
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\RouteType", inversedBy="routes")
     * @ORM\JoinColumn(name="route_type_id", nullable=false)
     */
    private RouteType $routeType;

    public function __toString()
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'coordinates' => $this->coordinates,
            'distance' => $this->distance,
            'forcedDistance' => $this->forcedDistance,
            'guarded' => $this->guarded,
            'markerStart' => $this->markerStart ? $this->markerStart->getId() : null,
            'markerEnd' => $this->markerEnd ? $this->markerEnd->getId() : null,
            'map' => $this->map->getId(),
            'routeType' => $this->routeType->getId(),
            'faction' => $this->faction ? $this->faction->getId() : null,
            'isNoteFrom' => $this->isNoteFrom ? $this->isNoteFrom->getId() : null,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public static function fromApi(MapElementDTOInterface $dto): self
    {
        self::checkApiDTO($dto);

        $route = new self();

        $route->hydrateFromApi($dto);

        return $route;
    }

    public function updateFromApi(MapElementDTOInterface $dto): void
    {
        self::checkApiDTO($dto);

        $this->hydrateFromApi($dto);
    }

    /**
     * @return array|LatLng[]
     */
    public function getLatLngs(): array
    {
        $coords = $this->getDecodedCoordinates();

        $latlngs = [];

        foreach ($coords as $coord) {
            $latlngs[] = LatLng::create((float) $coord['lat'], (float) $coord['lng']);
        }

        return $latlngs;
    }

    public function getColor(): string
    {
        return $this->routeType->getColor();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function getDescription(): string
    {
        return (string) $this->description;
    }

    public function getCoordinates(): string
    {
        return (string) $this->coordinates;
    }

    public function getMap(): ?Map
    {
        return $this->map;
    }

    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    public function getRouteType(): ?RouteType
    {
        return $this->routeType;
    }

    public function getMarkerStart(): ?Marker
    {
        return $this->markerStart;
    }

    public function getMarkerEnd(): ?Marker
    {
        return $this->markerEnd;
    }

    public function getDistance(): float
    {
        return (float) $this->distance;
    }

    public function getForcedDistance(): ?float
    {
        return $this->forcedDistance;
    }

    public function isGuarded(): bool
    {
        return (bool) $this->guarded;
    }

    public function getDecodedCoordinates(): array
    {
        return (array) \json_decode($this->coordinates, true);
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function refresh(): void
    {
        if (!$this->refresh) {
            return;
        }

        if (!$this->coordinates) {
            $this->coordinates = '[]';
        }
        $coords = \json_decode($this->coordinates, true);

        if ($this->markerStart && isset($coords[0])) {
            $coords[0] = [
                'lat' => $this->markerStart->getLatitude(),
                'lng' => $this->markerStart->getLongitude(),
            ];
        }
        if ($this->markerEnd) {
            $coords[\count($coords) - ((int) (\count($coords) > 1))] = [
                'lat' => $this->markerEnd->getLatitude(),
                'lng' => $this->markerEnd->getLongitude(),
            ];
        }

        $this->calcDistance();

        $this->setCoordinates(\json_encode($coords));
    }

    public function calcDistance(): float
    {
        // Override distance if we have set forcedDistance
        if ($this->forcedDistance) {
            $this->distance = $this->forcedDistance;

            return $this->forcedDistance;
        }

        // Else, we force the null value
        $this->forcedDistance = null;

        $distance = 0;
        $points = \json_decode($this->coordinates, true);

        \reset($points);

        // Use classic Pythagore's theorem to calculate distances.
        while ($current = \current($points)) {
            $next = \next($points);
            if (false !== $next) {
                $currentX = $current['lng'];
                $currentY = $current['lat'];
                $nextX = $next['lng'];
                $nextY = $next['lat'];

                $distance += \sqrt(
                    ($nextX * $nextX)
                    - (2 * $currentX * $nextX)
                    + ($currentX * $currentX)
                    + ($nextY * $nextY)
                    - (2 * $currentY * $nextY)
                    + ($currentY * $currentY)
                );
            }
        }

        // Apply map ratio to distance.
        $distance = $this->map->getCoordinatesRatio() * $distance;

        /**
         * The "substr" trick truncates the numbers, else mysql 5.7 would throw a warning.
         * This parameter should depend on the "precision" specified in the $distance property.
         *
         * @see Route::$distance
         */
        $floatPrecision = 12;

        $distance = (float) \mb_substr((string) $distance, 0, $floatPrecision);

        if ($distance !== (int) \mb_substr((string) $this->distance, 0, $floatPrecision)) {
            $this->distance = $distance;
        }

        return $distance;
    }

    private function setCoordinates(string $coordinates): self
    {
        try {
            \json_decode($coordinates, true);
        } catch (\Throwable $e) {
        }

        if (\JSON_ERROR_NONE !== $code = \json_last_error()) {
            throw new \InvalidArgumentException($code.':'.\json_last_error_msg());
        }

        $this->coordinates = $coordinates;

        $this->calcDistance();

        return $this;
    }

    private function hydrateFromApi(RouteDTO $dto): void
    {
        $this->name = $dto->name;
        $this->description = $dto->description;
        $this->distance = $dto->distance;
        $this->forcedDistance = $dto->forcedDistance;
        $this->guarded = $dto->guarded;
        $this->map = $dto->map;
        $this->markerStart = $dto->markerStart;
        $this->markerEnd = $dto->markerEnd;
        $this->faction = $dto->faction;
        $this->routeType = $dto->routeType;
        $this->isNoteFrom = $dto->isNoteFrom;
        $this->setCoordinates($dto->coordinates);

        $this->calcDistance();
    }

    private static function checkApiDTO(MapElementDTOInterface $dto): void
    {
        if (!$dto instanceof RouteDTO) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Api DTO must implemment %s, %s given.',
                    RouteDTO::class,
                    \get_debug_type($dto)
                )
            );
        }
    }
}
