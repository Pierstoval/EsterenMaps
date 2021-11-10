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
use EsterenMaps\Entity\Zone;
use Orbitale\Component\ArrayFixture\ArrayFixture;

class ZonesFixtures extends ArrayFixture implements ORMFixtureInterface, DependentFixtureInterface
{
    public const ID_DEFAULT_KINGDOM = 1;

    public function getDependencies()
    {
        return [
            MapsFixtures::class,
            ZonesTypesFixtures::class,
            FactionsFixtures::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityClass(): string
    {
        return Zone::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getReferencePrefix(): ?string
    {
        return 'esterenmaps-zones-';
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjects(): array
    {
        $map1 = $this->getReference('esterenmaps-maps-tri-kazel');

//        $zoneType1 = $this->getReference('esterenmaps-zonestypes-1');// Political
        $zoneType2 = $this->getReference('esterenmaps-zonestypes-2'); // Kingdom

        $faction1 = $this->getReference('esterenmaps-factions-Faction Test');

        return [
            [
                'id' => self::ID_DEFAULT_KINGDOM,
                'map' => $map1,
                'faction' => $faction1,
                'zoneType' => $zoneType2,
                'name' => 'Kingdom test',
                'description' => '',
                'coordinates' => '[{"lat":25,"lng":35},{"lat":35,"lng":35},{"lat":35,"lng":40},{"lat":25,"lng":40}]',
                'createdAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-03-14 15:26:35'),
                'updatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', '2015-12-27 17:38:09'),
            ],
        ];
    }
}
