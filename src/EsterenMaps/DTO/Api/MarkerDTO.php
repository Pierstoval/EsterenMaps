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

namespace EsterenMaps\DTO\Api;

use EsterenMaps\Entity\Faction;
use EsterenMaps\Entity\Map;
use EsterenMaps\Entity\MapElementInterface;
use EsterenMaps\Entity\Marker;
use EsterenMaps\Entity\MarkerType;
use Symfony\Component\Validator\Constraints as Assert;
use User\Entity\User;

class MarkerDTO implements MapElementDTOInterface
{
    /**
     * @Assert\NotBlank
     */
    public string $name;

    public ?string $description;

    public float $altitude = 0.0;

    public float $latitude = 0.0;

    public float $longitude = 0.0;

    public ?Faction $faction;

    /**
     * @Assert\NotBlank
     */
    public ?Map $map;

    /**
     * @Assert\NotBlank
     */
    public ?MarkerType $markerType;

    public ?User $isNoteFrom = null;

    public static function fromApiPayload(array $payload): self
    {
        $self = new self();

        $self->hydrateFromPayload($payload);

        return $self;
    }

    public static function fromMapElementAndApiPayload(MapElementInterface $marker, array $payload): self
    {
        if (!$marker instanceof Marker) {
            throw new \InvalidArgumentException(\sprintf(
                'Map element for class %s must be an instance of %s.',
                self::class,
                Marker::class
            ));
        }

        $self = new self();

        $self->name = $marker->getName();
        $self->description = $marker->getDescription();
        $self->altitude = $marker->getAltitude();
        $self->latitude = $marker->getLatitude();
        $self->longitude = $marker->getLongitude();
        $self->faction = $marker->getFaction();
        $self->map = $marker->getMap();
        $self->markerType = $marker->getMarkerType();
        $self->isNoteFrom = $marker->isNoteFrom();

        $self->hydrateFromPayload($payload);

        return $self;
    }

    private function hydrateFromPayload(array $payload): void
    {
        foreach ($payload as $property => $value) {
            if (!\property_exists(self::class, $property)) {
                throw new \InvalidArgumentException(\sprintf(
                    "Property \"%s\" does not exist in class %s.\n".
                    'Did you forget to sanitize the payload before creating the DTO?',
                    $property,
                    self::class
                ));
            }

            $this->{$property} = $value;
        }
    }
}
