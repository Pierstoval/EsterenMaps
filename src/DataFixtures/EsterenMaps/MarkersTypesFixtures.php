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
use EsterenMaps\Entity\MarkerType;
use Orbitale\Component\ArrayFixture\ArrayFixture;

class MarkersTypesFixtures extends ArrayFixture implements ORMFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getEntityClass(): string
    {
        return MarkerType::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getReferencePrefix(): ?string
    {
        return 'esterenmaps-markerstypes-';
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjects(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'City',
                'description' => '',
                'icon' => 'pastille-beige.png',
                'iconWidth' => 16,
                'iconHeight' => 16,
                'iconCenterX' => null,
                'iconCenterY' => null,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-17 22:51:49'),
            ],
            [
                'id' => 2,
                'name' => 'Shipyard',
                'description' => '',
                'icon' => 'pastille-blue.png',
                'iconWidth' => 16,
                'iconHeight' => 16,
                'iconCenterX' => null,
                'iconCenterY' => null,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-17 22:51:49'),
            ],
            [
                'id' => 3,
                'name' => 'Village',
                'description' => '',
                'icon' => 'pastille-green.png',
                'iconWidth' => 16,
                'iconHeight' => 16,
                'iconCenterX' => null,
                'iconCenterY' => null,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:22'),
            ],
            [
                'id' => 4,
                'name' => 'Invisible',
                'description' => '',
                'icon' => 'invisible.png',
                'iconWidth' => 16,
                'iconHeight' => 16,
                'iconCenterX' => null,
                'iconCenterY' => null,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
            ],
        ];
    }
}
