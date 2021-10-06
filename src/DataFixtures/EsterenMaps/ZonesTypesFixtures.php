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
use Doctrine\ORM\EntityManagerInterface;
use EsterenMaps\Entity\ZoneType;
use Orbitale\Component\ArrayFixture\ArrayFixture;

class ZonesTypesFixtures extends ArrayFixture implements ORMFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getEntityClass(): string
    {
        return ZoneType::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getReferencePrefix(): ?string
    {
        return 'esterenmaps-zonestypes-';
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjects(): array
    {
        return [
            [
                'id' => 1,
                'parent' => null,
                'name' => 'Political',
                'description' => '',
                'color' => '',
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
            ],
            [
                'id' => 2,
                'parent' => static function (ZoneType $obj, ArrayFixture $f, EntityManagerInterface $manager) {
                    $ref = $manager->find(ZoneType::class, 1);
                    $obj->setParent($ref);

                    return $ref;
                },
                'name' => 'Kingdom',
                'description' => '',
                'color' => '#E05151',
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
            ],
        ];
    }
}
