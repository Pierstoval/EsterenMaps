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
use EsterenMaps\Entity\Marker;
use EsterenMaps\Entity\Route;
use EsterenMaps\Entity\RouteType;
use Symfony\Component\Validator\Constraints as Assert;
use User\Entity\User;

class RouteDTO implements MapElementDTOInterface
{
    /**
     * @Assert\NotBlank
     */
    public ?string $name = null;

    public ?string $description = null;

    public ?string $coordinates = '';

    /**
     * @Assert\GreaterThanOrEqual(0)
     */
    public ?float $distance = 0;

    /**
     * @Assert\GreaterThanOrEqual(0)
     */
    public ?float $forcedDistance = null;

    public ?bool $guarded = false;

    /**
     * @Assert\NotBlank
     */
    public ?Map $map = null;

    public ?Marker $markerStart = null;

    public ?Marker $markerEnd = null;

    public ?Faction $faction = null;

    /**
     * @Assert\NotBlank
     */
    public ?RouteType $routeType = null;

    public ?User $isNoteFrom = null;

    public static function fromApiPayload(array $payload): self
    {
        $self = new self();

        $self->hydrateFromPayload($payload);

        return $self;
    }

    public static function fromMapElementAndApiPayload(MapElementInterface $route, array $payload): self
    {
        if (!$route instanceof Route) {
            throw new \InvalidArgumentException(\sprintf(
                'Map element for class %s must be an instance of %s.',
                self::class,
                Route::class
            ));
        }

        $self = new self();

        $self->name = $route->getName();
        $self->description = $route->getDescription();
        $self->coordinates = $route->getCoordinates();
        $self->distance = $route->getDistance();
        $self->forcedDistance = $route->getForcedDistance();
        $self->guarded = $route->isGuarded();
        $self->map = $route->getMap();
        $self->markerStart = $route->getMarkerStart();
        $self->markerEnd = $route->getMarkerEnd();
        $self->faction = $route->getFaction();
        $self->routeType = $route->getRouteType();
        $self->isNoteFrom = $route->isNoteFrom();

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
