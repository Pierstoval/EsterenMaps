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

namespace EsterenMaps\DTO\Api;

use EsterenMaps\Entity\Faction;
use EsterenMaps\Entity\Map;
use EsterenMaps\Entity\MapElementInterface;
use EsterenMaps\Entity\Zone;
use EsterenMaps\Entity\ZoneType;
use Symfony\Component\Validator\Constraints as Assert;
use User\Entity\User;

class ZoneDTO implements MapElementDTOInterface
{
    /**
     * @Assert\NotBlank
     */
    public ?string $name = null;

    public ?string $description = null;

    public ?string $coordinates = '';

    /**
     * @Assert\NotBlank
     */
    public ?Map $map = null;

    public ?Faction $faction = null;

    /**
     * @Assert\NotBlank
     */
    public ?ZoneType $zoneType = null;

    public ?User $isNoteFrom = null;

    public static function fromApiPayload(array $payload): self
    {
        $self = new self();

        $self->hydrateFromPayload($payload);

        return $self;
    }

    public static function fromMapElementAndApiPayload(MapElementInterface $zone, array $payload): self
    {
        if (!$zone instanceof Zone) {
            throw new \InvalidArgumentException(\sprintf(
                'Map element for class %s must be an instance of %s.',
                self::class,
                Zone::class
            ));
        }

        $self = new self();

        $self->name = $zone->getName();
        $self->description = $zone->getDescription();
        $self->coordinates = $zone->getCoordinates();
        $self->map = $zone->getMap();
        $self->faction = $zone->getFaction();
        $self->zoneType = $zone->getZoneType();
        $self->isNoteFrom = $zone->isNoteFrom();

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
