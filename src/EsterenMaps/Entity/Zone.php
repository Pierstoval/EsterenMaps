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

use Doctrine\ORM\Mapping as ORM;
use EsterenMaps\DTO\Api\MapElementDTOInterface;
use EsterenMaps\DTO\Api\ZoneDTO;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="maps_zones")
 * @ORM\Entity(repositoryClass="EsterenMaps\Repository\ZonesRepository")
 */
class Zone implements \JsonSerializable, MapElementInterface
{
    use CanBeUserNote;
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     *
     * @Assert\NotBlank
     */
    protected string $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Assert\Type("string")
     */
    protected ?string $description;

    /**
     * @ORM\Column(name="coordinates", type="text")
     *
     * @Assert\Type("string")
     */
    protected string $coordinates = '';

    /**
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\Map")
     * @ORM\JoinColumn(name="map_id", nullable=false)
     *
     * @Assert\Type("EsterenMaps\Entity\Map")
     * @Assert\NotBlank
     */
    protected Map $map;

    /**
     * @var Faction
     *
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\Faction")
     * @ORM\JoinColumn(name="faction_id", nullable=true)
     *
     * @Assert\Type("EsterenMaps\Entity\Faction")
     */
    protected ?Faction $faction;

    /**
     * @ORM\ManyToOne(targetEntity="EsterenMaps\Entity\ZoneType", inversedBy="zones")
     * @ORM\JoinColumn(name="zone_type_id", nullable=false)
     *
     * @Assert\Type("EsterenMaps\Entity\ZoneType")
     * @Assert\NotBlank
     */
    protected ZoneType $zoneType;

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'coordinates' => $this->coordinates,
            'map' => $this->map->getId(),
            'zoneType' => $this->zoneType->getId(),
            'faction' => $this->faction ? $this->faction->getId() : null,
            'isNoteFrom' => $this->isNoteFrom ? $this->isNoteFrom->getId() : null,
        ];
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
        return $this->coordinates;
    }

    public function getMap(): ?Map
    {
        return $this->map;
    }

    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    public function getZoneType(): ?ZoneType
    {
        return $this->zoneType;
    }

    public function getDecodedCoordinates(): array
    {
        return \json_decode($this->coordinates, true) ?: [];
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

        return $this;
    }

    private function hydrateFromApi(ZoneDTO $dto): void
    {
        $this->name = $dto->name;
        $this->description = $dto->description;
        $this->map = $dto->map;
        $this->faction = $dto->faction;
        $this->zoneType = $dto->zoneType;
        $this->isNoteFrom = $dto->isNoteFrom;
        $this->setCoordinates($dto->coordinates);
    }

    private static function checkApiDTO(MapElementDTOInterface $dto): void
    {
        if (!$dto instanceof ZoneDTO) {
            throw new \InvalidArgumentException(\sprintf(
                'Api DTO must implemment %s, %s given.',
                ZoneDTO::class,
                \get_debug_type($dto)
            ));
        }
    }
}
