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
use EsterenMaps\Entity\Faction;
use EsterenMaps\Entity\Map;
use EsterenMaps\Entity\Marker;
use EsterenMaps\Entity\MarkerType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MarkerAdminDTO implements EasyAdminDTOInterface, TranslatableDTOInterface
{
    use TranslatableDTOTrait;

    /**
     * @var string[]
     *
     * @Assert\NotBlank
     * @Assert\All(constraints={
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     */
    public ?array $translatedNames = [];

    /**
     * @var string[]
     *
     * @Assert\NotBlank
     * @Assert\All(constraints={
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     */
    public ?array $translatedDescriptions = [];

    public ?float $latitude = 0;
    public ?float $longitude = 0;
    public ?float $altitude = 0;

    public ?Faction $faction = null;
    public ?Map $map = null;
    public ?MarkerType $markerType = null;

    public static function getTranslatableFields(): array
    {
        return [
            'name' => 'translatedNames',
            'description' => 'translatedDescriptions',
        ];
    }

    public static function createFromEntity(object $entity, array $options = []): self
    {
        if (!$entity instanceof Marker) {
            throw new UnexpectedTypeException($entity, Marker::class);
        }

        $self = new self();

        $self->setTranslationsFromEntity($entity, $options);

        $self->faction = $entity->getFaction();
        $self->map = $entity->getMap();
        $self->markerType = $entity->getMarkerType();
        $self->latitude = $entity->getLatitude();
        $self->longitude = $entity->getLongitude();
        $self->altitude = $entity->getAltitude();

        return $self;
    }

    public static function createEmpty(): self
    {
        return new self();
    }
}
