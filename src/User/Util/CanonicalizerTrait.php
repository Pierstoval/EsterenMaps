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

namespace User\Util;

trait CanonicalizerTrait
{
    public function canonicalize(string $string): string
    {
        return Canonicalizer::urlize($string);
    }
}
