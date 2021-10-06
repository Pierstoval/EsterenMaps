<?php

declare(strict_types=1);

/*
 * This file is part of the Agate Apps package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com> and Studio Agate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EsterenMaps\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use EsterenMaps\DTO\Api\MapElementDTOInterface;
use EsterenMaps\DTO\Api\MarkerDTO;
use EsterenMaps\DTO\MarkerAdminDTO;
use EsterenMaps\Model\LatLng;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Markers.
 *
 * @ORM\Table(name="maps_markers")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="EsterenMaps\Repository\MarkersRepository")
 */
class Marker implements \JsonSerializable, MapElementInterface
{
    use CanBeUserNote;
    use TimestampableEntity;

    /**
     * @Gedmo\Locale
     */
    protected ?string $locale = null;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     *
     * @Gedmo\Translatable
     */
    private string $name;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Gedmo\Translatable
     */
    private ?string $description = null;

    /**
     * @ORM\Column(name="altitude", type="string", length=255, options={"default" = 0})
     */
    private string $altitude = '0';

    /**
     * @ORM\Column(name="latitude", type="string", length=255, options={"default" = 0})
     */
    private string $latitude = '0';

    /**
     * @ORM\Column(name="longitude", type="string", length=255, options={"default" = 0})
     */
    private string $longitude = '0';

    /**
     * @var Faction
     *
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\Faction")
     * @ORM\JoinColumn(name="faction_id", nullable=true)
     */
    private ?Faction $faction;

    /**
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\Map")
     * @ORM\JoinColumn(name="map_id", nullable=false)
     */
    private Map $map;

    /**
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\MarkerType", inversedBy="markers")
     * @ORM\JoinColumn(name="marker_type_id", nullable=false)
     */
    private MarkerType $markerType;

    /**
     * @var Collection|Route[]
     *
     * @ORM\OneToMany(targetEntity="EsterenMaps\Entity\Route", mappedBy="markerStart")
     */
    private $routesStart;

    /**
     * @var Collection|Route[]
     *
     * @ORM\OneToMany(targetEntity="EsterenMaps\Entity\Route", mappedBy="markerEnd")
     */
    private $routesEnd;

    /**
     * If true, the self::updateRoutesCoordinates() method will force the update of the associated routes.
     */
    private bool $forceRoutesUpdate = false;

    public function __construct()
    {
        $this->routesStart = new ArrayCollection();
        $this->routesEnd = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public static function fromAdmin(MarkerAdminDTO $dto, string $defaultLocale): self
    {
        throw new \RuntimeException('Cannot create a marker from admin panel.');
    }

    public function updateFromAdmin(MarkerAdminDTO $dto, string $defaultLocale): void
    {
        $this->locale = $defaultLocale;
        $this->name = $dto->translatedNames[$defaultLocale];
        $this->description = $dto->translatedDescriptions[$defaultLocale];
        $this->faction = $dto->faction;
        $this->map = $dto->map;
        $this->markerType = $dto->markerType;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'altitude' => (float) $this->altitude,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'faction' => $this->faction ? $this->faction->getId() : null,
            'map' => $this->map->getId(),
            'markerType' => $this->markerType->getId(),
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

    public function getLatLng(): LatLng
    {
        return LatLng::create($this->getLatitude(), $this->getLongitude());
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): int
    {
        return (int) $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    public function getMap(): ?Map
    {
        return $this->map;
    }

    public function getMarkerType(): ?MarkerType
    {
        return $this->markerType;
    }

    public function getAltitude(): float
    {
        return (float) $this->altitude;
    }

    public function getLatitude(): float
    {
        return (float) $this->latitude;
    }

    public function getLongitude(): float
    {
        return (float) $this->longitude;
    }

    public function getDescription(): string
    {
        return (string) $this->description;
    }

    public function getWebIcon(): string
    {
        return $this->markerType->getWebIcon();
    }

    public function getIconWidth(): int
    {
        return $this->markerType->getIconWidth();
    }

    public function getIconHeight(): int
    {
        return $this->markerType->getIconHeight();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateRoutesCoordinates(): void
    {
        foreach ($this->routesStart as $route) {
            $route->refresh = $this->forceRoutesUpdate;
            $route->refresh();
        }

        foreach ($this->routesEnd as $route) {
            $route->refresh = $this->forceRoutesUpdate;
            $route->refresh();
        }
    }

    private function hydrateFromApi(MarkerDTO $dto): void
    {
        $this->name = $dto->name;
        $this->description = $dto->description;
        $this->altitude = (string) $dto->altitude;
        $this->latitude = (string) $dto->latitude;
        $this->longitude = (string) $dto->longitude;
        $this->faction = $dto->faction;
        $this->map = $dto->map;
        $this->markerType = $dto->markerType;
        $this->isNoteFrom = $dto->isNoteFrom;

        $this->forceRoutesUpdate = true;
        $this->updateRoutesCoordinates();
    }

    private static function checkApiDTO(MapElementDTOInterface $dto): void
    {
        if (!$dto instanceof MarkerDTO) {
            throw new \InvalidArgumentException(\sprintf(
                'Api DTO must implemment %s, %s given.',
                MarkerDTO::class,
                \get_debug_type($dto)
            ));
        }
    }
}
