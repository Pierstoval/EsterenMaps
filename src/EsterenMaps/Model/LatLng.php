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

namespace EsterenMaps\Model;

final class LatLng
{
    /**
     * @var float
     */
    private $lat;

    /**
     * @var float
     */
    private $lng;

    private function __construct()
    {
    }

    public static function create(float $lat, float $lng): self
    {
        $self = new self();

        $self->lat = $lat;
        $self->lng = $lng;

        return $self;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }
}
