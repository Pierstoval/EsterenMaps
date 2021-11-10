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
use EsterenMaps\DTO\MapAdminDTO;
use EsterenMaps\Model\MapBounds;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="maps")
 * @ORM\Entity(repositoryClass="EsterenMaps\Repository\MapsRepository")
 */
class Map
{
    use TimestampableEntity;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @Gedmo\Translatable
     */
    protected string $name;

    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(type="string", length=255, nullable=false, unique=true)
     */
    protected string $nameSlug;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Gedmo\Translatable
     */
    protected string $description;

    /**
     * @ORM\Column(name="image", type="string", length=255, nullable=false)
     */
    protected string $image;

    /**
     * @ORM\Column(name="max_zoom", type="smallint", options={"default" = 1})
     * @Assert\Range(
     *     min=1,
     *     max=50
     * )
     */
    protected int $maxZoom = 10;

    /**
     * @ORM\Column(name="start_zoom", type="smallint", options={"default" = 1})
     * @Assert\Range(
     *     min=1,
     *     max=10
     * )
     */
    protected int $startZoom = 10;

    /**
     * @ORM\Column(name="start_x", type="smallint", options={"default" = 1})
     */
    protected int $startX = 0;

    /**
     * @ORM\Column(name="start_y", type="smallint", options={"default" = 1})
     */
    protected int $startY = 0;

    /**
     * @ORM\Column(name="bounds", type="string", options={"default" = "[]"})
     */
    protected string $bounds = '[]';

    /**
     * @ORM\Column(name="coordinates_ratio", type="smallint", options={"default" = 1})
     */
    protected int $coordinatesRatio = 1;

    /**
     * @var string
     *
     * @Gedmo\Locale
     */
    private ?string $locale = null;

    private function __construct()
    {
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function fromAdmin(MapAdminDTO $dto): self
    {
        $self = new self();

        $self->updateFromAdminDTO($dto);

        return $self;
    }

    public function updateFromAdmin(MapAdminDTO $dto): void
    {
        $this->updateFromAdminDTO($dto);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getMaxZoom(): int
    {
        return $this->maxZoom;
    }

    public function getNameSlug(): string
    {
        return $this->nameSlug;
    }

    public function getStartZoom(): int
    {
        return $this->startZoom;
    }

    public function getStartX(): int
    {
        return $this->startX;
    }

    public function getStartY(): int
    {
        return $this->startY;
    }

    public function getBounds(): string
    {
        return $this->bounds;
    }

    public function getCoordinatesRatio(): int
    {
        return $this->coordinatesRatio;
    }

    public function getObjectBounds(): MapBounds
    {
        return MapBounds::fromMap($this);
    }

    public function getArrayBounds(): array
    {
        return \json_decode($this->bounds, true);
    }

    private function updateFromAdminDTO(MapAdminDTO $dto): void
    {
        // Always save base translation in French
        $this->locale = 'fr';
        $this->name = $dto->translatedNames['fr'];
        $this->description = $dto->translatedDescriptions['fr'];

        $this->maxZoom = $dto->maxZoom;
        $this->startZoom = $dto->startZoom;
        $this->startX = $dto->startX;
        $this->startY = $dto->startY;
        $this->bounds = $dto->bounds;

        if ($dto->imagePath) {
            $this->image = $dto->imagePath;
        }
    }
}
