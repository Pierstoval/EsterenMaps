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

namespace DataFixtures\EsterenMaps;

use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use EsterenMaps\Entity\Map;
use Orbitale\Component\ArrayFixture\ArrayFixture;

class MapsFixtures extends ArrayFixture implements ORMFixtureInterface
{
    protected function getEntityClass(): string
    {
        return Map::class;
    }

    protected function getReferencePrefix(): ?string
    {
        return 'esterenmaps-maps-';
    }

    protected function getMethodNameForReference(): string
    {
        return 'getNameSlug';
    }

    protected function getObjects(): array
    {
        return [
            [
                'name' => 'Tri-Kazel',
                'nameSlug' => 'tri-kazel',
                'image' => 'uploads/maps/esteren_map.jpg',
                'description' => 'Carte de Tri-Kazel officielle, réalisée par Chris.',
                'maxZoom' => 5,
                'startZoom' => 2,
                'startX' => 65,
                'startY' => 85,
                'coordinatesRatio' => 5,
                'bounds' => '[[132.85,-1],[-1,169.77]]',
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-23 14:46:09'),
            ],
        ];
    }
}
