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
use EsterenMaps\Entity\TransportType;
use Orbitale\Component\ArrayFixture\ArrayFixture;

class TransportTypesFixtures extends ArrayFixture implements ORMFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getEntityClass(): string
    {
        return TransportType::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getReferencePrefix(): ?string
    {
        return 'esterenmaps-transports-';
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjects(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Any transport',
                'slug' => 'any-transport',
                'description' => 'Any transport',
                'speed' => 4.5,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:25'),
            ],
            [
                'id' => 2,
                'name' => 'Cart',
                'slug' => 'cart',
                'description' => '',
                'speed' => 8,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
            ],
            [
                'id' => 3,
                'name' => 'Horse',
                'slug' => 'horse',
                'description' => '',
                'speed' => 12,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
            ],
            [
                'id' => 4,
                'name' => 'Caernide',
                'slug' => 'caernide',
                'description' => '',
                'speed' => 12,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:05'),
            ],
            [
                'id' => 5,
                'name' => 'Small boat',
                'slug' => 'small-boat',
                'description' => 'On rivers or sea',
                'speed' => 4,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 12:24:05'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:15'),
            ],
            [
                'id' => 6,
                'name' => 'Koggen (boat)',
                'slug' => 'koggen',
                'description' => 'On rivers or sea',
                'speed' => 16,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 12:25:11'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:09'),
            ],
        ];
    }
}
