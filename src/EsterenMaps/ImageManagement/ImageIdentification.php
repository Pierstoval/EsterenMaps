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

namespace EsterenMaps\ImageManagement;

/**
 * This class stores the attributes of an identification made by the tiles generator.
 */
class ImageIdentification implements \ArrayAccess
{
    private const VALID_PROPERTIES = [
        'xmax',
        'ymax',
        'tiles_max',
        'wmax',
        'hmax',
        'wmax_global',
        'hmax_global',
    ];

    private array $properties = [];

    public function __construct(array $data = [])
    {
        $this->properties = \array_merge(\array_fill_keys(self::VALID_PROPERTIES, null), $data);
    }

    public function getTilesX(): int
    {
        return $this->properties['xmax'];
    }

    public function getTilesY(): int
    {
        return $this->properties['ymax'];
    }

    public function getTiles(): int
    {
        return $this->properties['tiles_max'];
    }

    public function getWidth(): float
    {
        return $this->properties['wmax'];
    }

    public function getHeight(): float
    {
        return $this->properties['hmax'];
    }

    public function getGlobalWidth(): float
    {
        return $this->properties['wmax_global'];
    }

    public function getGlobalHeight(): float
    {
        return $this->properties['hmax_global'];
    }

    public function offsetExists($offset): bool
    {
        return \in_array($offset, self::VALID_PROPERTIES, true) && \array_key_exists($offset, $this->properties);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->properties[$offset] : null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($this->offsetExists($offset)) {
            $this->properties[$offset] = $value;
        } else {
            throw new \RuntimeException(\sprintf(
                'Undefined attribute %s in ImageIdentification',
                $offset
            ));
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->properties[$offset]);
    }
}
