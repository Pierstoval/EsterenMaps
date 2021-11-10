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

namespace EsterenMaps\DTO;

use Admin\DTO\EasyAdminDTOInterface;
use Admin\DTO\TranslatableDTOInterface;
use Admin\DTO\TranslatableDTOTrait;
use EsterenMaps\Entity\Map;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MapAdminDTO implements EasyAdminDTOInterface, TranslatableDTOInterface
{
    use TranslatableDTOTrait;

    /**
     * @Assert\NotBlank
     * @Assert\All(constraints={
     *     @Assert\NotBlank
     * })
     */
    public ?array $translatedNames = [];

    /**
     * @Assert\NotBlank
     * @Assert\All(constraints={
     *     @Assert\NotBlank
     * })
     */
    public ?array $translatedDescriptions = [];

    public ?UploadedFile $image = null;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\Range(
     *     min=1,
     *     max=50
     * )
     */
    public ?int $maxZoom = 10;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\Range(
     *     min=1,
     *     max=10
     * )
     */
    public ?int $startZoom = 1;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\Range(min=0)
     */
    public ?int $startX = 1;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\Range(min=0)
     */
    public ?int $startY = 1;

    public ?string $bounds = '[]';

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\Range(min=0)
     */
    public ?int $coordinatesRatio = 1;

    public ?string $imagePath = null;

    private string $nameSlug = '';

    private function __construct()
    {
    }

    public function getNameSlug(): string
    {
        return $this->nameSlug;
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public static function createFromEntity(object $map, array $options = []): self
    {
        if (!$map instanceof Map) {
            throw new UnexpectedTypeException($map, Map::class);
        }

        $self = new self();

        $self->setTranslationsFromEntity($map, $options);

        $self->nameSlug = $map->getNameSlug();
        $self->maxZoom = $map->getMaxZoom();
        $self->startZoom = $map->getStartZoom();
        $self->startX = $map->getStartX();
        $self->startY = $map->getStartY();
        $self->coordinatesRatio = $map->getCoordinatesRatio();
        $self->bounds = $map->getBounds();

        return $self;
    }

    public static function getTranslatableFields(): array
    {
        return [
            'name' => 'translatedNames',
            'description' => 'translatedDescriptions',
        ];
    }
}
