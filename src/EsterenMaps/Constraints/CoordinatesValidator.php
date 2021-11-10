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

namespace EsterenMaps\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CoordinatesValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Coordinates) {
            throw new UnexpectedTypeException($constraint, Coordinates::class);
        }

        if (\is_string($value)) {
            $value = \json_decode($value, true);
            if (false === $value) {
                $this->context->addViolation('This value should be a valid JSON string.');

                return;
            }
        }

        if (!\is_array($value)) {
            $this->context->addViolation('JSON string must contain an array');

            return;
        }

        foreach ($value as $item) {
            if (2 !== \count($item)) {
                $this->context->addViolation('Coordinates must be an array of 2 items: latitude and longitude, either with keys 0 and 1 or "lat" and "lng".');

                break;
            }

            if (isset($item['lat'], $item['lng'])) {
                $keyLat = 'lat';
                $keyLng = 'lng';
            } elseif (isset($item[0], $item[1])) {
                $keyLat = 0;
                $keyLng = 1;
            } else {
                $this->context->addViolation('Keys for latitude and longitude must be either "lat" and "lng" or "0" and "1".');

                return;
            }

            if (!\is_numeric($item[$keyLat])) {
                $this->context->addViolation('Latitude must be a valid number');

                break;
            }

            if (!\is_numeric($item[$keyLng])) {
                $this->context->addViolation('Longitude must be a valid number');

                break;
            }
        }
    }
}
