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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use EsterenMaps\DTO\MarkerTypeAdminDTO;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * MarkersType.
 *
 * @ORM\Table(name="maps_markers_types")
 * @ORM\Entity(repositoryClass="EsterenMaps\Repository\MarkersTypesRepository")
 */
class MarkerType
{
    use TimestampableEntity;

    public const PUBLIC_PATH_BASE = '/build/markerstypes/';

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=true)
     *
     * @Gedmo\Translatable
     */
    protected string $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Gedmo\Translatable
     */
    protected ?string $description;

    /**
     * @ORM\Column(name="icon", type="string", length=255, nullable=false)
     */
    protected string $icon;

    /**
     * @ORM\Column(name="icon_width", type="integer")
     */
    protected int $iconWidth = 0;

    /**
     * @ORM\Column(name="icon_height", type="integer")
     */
    protected int $iconHeight = 0;

    /**
     * @var int
     * @ORM\Column(name="icon_center_x", type="integer", nullable=true)
     */
    protected ?int $iconCenterX;

    /**
     * @var int
     * @ORM\Column(name="icon_center_y", type="integer", nullable=true)
     */
    protected ?int $iconCenterY;

    /**
     * @var Collection|Marker[]
     *
     * @ORM\OneToMany(targetEntity="EsterenMaps\Entity\Marker", mappedBy="markerType")
     */
    protected iterable $markers;

    /**
     * @Gedmo\Locale
     */
    protected ?string $locale = null;

    private function __construct()
    {
        $this->markers = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function fromAdmin(MarkerTypeAdminDTO $dto, string $defaultLocale): self
    {
        $self = new self();

        $self->hydrateFromAdminDTO($dto, $defaultLocale);

        return $self;
    }

    public function updateFromAdmin(MarkerTypeAdminDTO $dto, string $defaultLocale): void
    {
        $this->hydrateFromAdminDTO($dto, $defaultLocale);
    }

    public function getId(): int
    {
        return (int) $this->id;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @return Marker[]
     */
    public function getMarkers(): iterable
    {
        return $this->markers;
    }

    public function getDescription(): string
    {
        return (string) $this->description;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getWebIcon(): string
    {
        return self::PUBLIC_PATH_BASE.$this->icon;
    }

    public function getIconWidth(): int
    {
        return (int) $this->iconWidth;
    }

    public function getIconHeight(): int
    {
        return (int) $this->iconHeight;
    }

    public function getIconCenterX(): ?int
    {
        return $this->iconCenterX;
    }

    public function getIconCenterY(): ?int
    {
        return $this->iconCenterY;
    }

    private function hydrateFromAdminDTO(MarkerTypeAdminDTO $dto, string $defaultLocale): void
    {
        $this->locale = $defaultLocale;
        $this->name = $dto->translatedNames[$defaultLocale];
        $this->description = $dto->translatedDescriptions[$defaultLocale];
        $this->iconWidth = $dto->iconWidth;
        $this->iconHeight = $dto->iconHeight;
        $this->iconCenterX = $dto->iconCenterX;
        $this->iconCenterY = $dto->iconCenterY;

        if ($iconName = $dto->getUploadedFileName()) {
            $this->icon = $iconName;
        }
    }
}
