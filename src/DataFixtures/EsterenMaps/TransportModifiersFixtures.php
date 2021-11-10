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
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use EsterenMaps\Entity\TransportModifier;
use Orbitale\Component\ArrayFixture\ArrayFixture;

class TransportModifiersFixtures extends ArrayFixture implements ORMFixtureInterface, DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            RoutesTypesFixtures::class,
            TransportTypesFixtures::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityClass(): string
    {
        return TransportModifier::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjects(): array
    {
        $routeType1 = $this->getReference('esterenmaps-routestypes-1');
        $routeType2 = $this->getReference('esterenmaps-routestypes-2');
        $routeType3 = $this->getReference('esterenmaps-routestypes-3');
        $routeType4 = $this->getReference('esterenmaps-routestypes-4');
        $routeType5 = $this->getReference('esterenmaps-routestypes-5');

        $transportType1 = $this->getReference('esterenmaps-transports-1');
        $transportType2 = $this->getReference('esterenmaps-transports-2');
        $transportType3 = $this->getReference('esterenmaps-transports-3');
        $transportType4 = $this->getReference('esterenmaps-transports-4');
        $transportType5 = $this->getReference('esterenmaps-transports-5');
        $transportType6 = $this->getReference('esterenmaps-transports-6');

        return [
            [
                'id' => 1,
                'routeType' => $routeType3,
                'transportType' => $transportType4,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
            ],
            [
                'id' => 2,
                'routeType' => $routeType3,
                'transportType' => $transportType3,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
            ],
            [
                'id' => 3,
                'routeType' => $routeType3,
                'transportType' => $transportType2,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
            ],
            [
                'id' => 4,
                'routeType' => $routeType3,
                'transportType' => $transportType1,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:25'),
            ],
            [
                'id' => 5,
                'routeType' => $routeType2,
                'transportType' => $transportType4,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
            ],
            [
                'id' => 6,
                'routeType' => $routeType2,
                'transportType' => $transportType3,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
            ],
            [
                'id' => 7,
                'routeType' => $routeType2,
                'transportType' => $transportType2,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
            ],
            [
                'id' => 8,
                'routeType' => $routeType2,
                'transportType' => $transportType1,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:25'),
            ],
            [
                'id' => 9,
                'routeType' => $routeType1,
                'transportType' => $transportType4,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
            ],
            [
                'id' => 10,
                'routeType' => $routeType1,
                'transportType' => $transportType3,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
            ],
            [
                'id' => 11,
                'routeType' => $routeType1,
                'transportType' => $transportType2,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
            ],
            [
                'id' => 12,
                'routeType' => $routeType1,
                'transportType' => $transportType1,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-07-10 20:49:06'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:25'),
            ],
            [
                'id' => 13,
                'routeType' => $routeType5,
                'transportType' => $transportType1,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 12:17:49'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:25'),
            ],
            [
                'id' => 14,
                'routeType' => $routeType5,
                'transportType' => $transportType2,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:30:21'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:30:21'),
            ],
            [
                'id' => 15,
                'routeType' => $routeType5,
                'transportType' => $transportType3,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:30:31'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:30:31'),
            ],
            [
                'id' => 16,
                'routeType' => $routeType5,
                'transportType' => $transportType4,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:30:38'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:30:38'),
            ],
            [
                'id' => 17,
                'routeType' => $routeType5,
                'transportType' => $transportType5,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:30:46'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:15'),
            ],
            [
                'id' => 18,
                'routeType' => $routeType5,
                'transportType' => $transportType6,
                'percentage' => 50,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:30:52'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:09'),
            ],
            [
                'id' => 19,
                'routeType' => $routeType1,
                'transportType' => $transportType5,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:31:14'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:15'),
            ],
            [
                'id' => 20,
                'routeType' => $routeType1,
                'transportType' => $transportType6,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:31:20'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:09'),
            ],
            [
                'id' => 21,
                'routeType' => $routeType2,
                'transportType' => $transportType5,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:31:34'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:15'),
            ],
            [
                'id' => 22,
                'routeType' => $routeType2,
                'transportType' => $transportType6,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:31:41'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:09'),
            ],
            [
                'id' => 23,
                'routeType' => $routeType3,
                'transportType' => $transportType5,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:31:47'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:15'),
            ],
            [
                'id' => 24,
                'routeType' => $routeType3,
                'transportType' => $transportType6,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:31:59'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:09'),
            ],
            [
                'id' => 25,
                'routeType' => $routeType4,
                'transportType' => $transportType1,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:32:41'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:25'),
            ],
            [
                'id' => 26,
                'routeType' => $routeType4,
                'transportType' => $transportType2,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:32:46'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:32:46'),
            ],
            [
                'id' => 27,
                'routeType' => $routeType4,
                'transportType' => $transportType3,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:32:51'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:32:51'),
            ],
            [
                'id' => 28,
                'routeType' => $routeType4,
                'transportType' => $transportType4,
                'percentage' => 0,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:32:57'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:32:57'),
            ],
            [
                'id' => 29,
                'routeType' => $routeType4,
                'transportType' => $transportType5,
                'percentage' => 50,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:33:02'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:15'),
            ],
            [
                'id' => 30,
                'routeType' => $routeType4,
                'transportType' => $transportType6,
                'percentage' => 100,
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-08 13:33:09'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2016-02-20 17:22:09'),
            ],
        ];
    }
}
