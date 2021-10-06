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

namespace DataFixtures\EsterenMaps;

use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use EsterenMaps\Entity\RouteType;
use Orbitale\Component\ArrayFixture\ArrayFixture;

class RoutesTypesFixtures extends ArrayFixture implements ORMFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getEntityClass(): string
    {
        return RouteType::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getReferencePrefix(): ?string
    {
        return 'esterenmaps-routestypes-';
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjects(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Track',
                'description' => '',
                'color' => 'rgba(165,110,52,1)',
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
            ],
            [
                'id' => 2,
                'name' => 'Road',
                'description' => '',
                'color' => 'rgba(199,191,183,1)',
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
            ],
            [
                'id' => 3,
                'name' => 'Trail',
                'description' => '',
                'color' => 'rgba(194,176,76,1)',
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
            ],
            [
                'id' => 4,
                'name' => 'Sea channel',
                'description' => '',
                'color' => 'rgba(64,148,220,0.4)',
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-09-24 10:52:59'),
            ],
            [
                'id' => 5,
                'name' => 'Fluvial way',
                'description' => '',
                'color' => 'rgba(64,220,191,0.4)',
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-09-24 10:52:41'),
            ],
        ];
    }
}
