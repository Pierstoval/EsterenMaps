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

use DateTime;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use EsterenMaps\Entity\Marker;
use Orbitale\Component\ArrayFixture\ArrayFixture;

class MarkersFixtures extends ArrayFixture implements ORMFixtureInterface, DependentFixtureInterface
{
    private $maps;
    private $markersTypes;
    private $factions;

    public function getDependencies()
    {
        return [
            FactionsFixtures::class,
            MapsFixtures::class,
            MarkersTypesFixtures::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityClass(): string
    {
        return Marker::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getReferencePrefix(): ?string
    {
        return 'esterenmaps-markers-';
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjects(): array
    {
        $this->maps = [
            1 => $this->getReference('esterenmaps-maps-tri-kazel'), // Tri-Kazel
        ];

        $this->markersTypes = [
            1 => $this->getReference('esterenmaps-markerstypes-1'), // City
            2 => $this->getReference('esterenmaps-markerstypes-2'), // Shipyard
            3 => $this->getReference('esterenmaps-markerstypes-3'), // Village
            4 => $this->getReference('esterenmaps-markerstypes-4'), // Invisible
        ];

        $this->factions = [
            1 => $this->getReference('esterenmaps-factions-Faction Test'), // Faction Test
        ];

        return [
            [
                'id' => 7,
                'faction' => $this->factions[1],
                'map' => $this->maps[1],
                'markerType' => $this->markersTypes[2],
                'name' => 'Tuaille',
                'description' => '',
                'routesStart' => new ArrayCollection(),
                'routesEnd' => new ArrayCollection(),
                'latitude' => '99.90832',
                'longitude' => '56.74192',
                'createdAt' => DateTime::createFromFormat('Y-m-d H:i:s', '2015-04-02 11:04:00'),
                'updatedAt' => DateTime::createFromFormat('Y-m-d H:i:s', '2015-12-27 17:25:52'),
            ],
            [
                'id' => 8,
                'faction' => $this->factions[1],
                'map' => $this->maps[1],
                'markerType' => $this->markersTypes[1],
                'name' => 'Osta-Baille',
                'description' => '',
                'routesStart' => new ArrayCollection(),
                'routesEnd' => new ArrayCollection(),
                'latitude' => '81.20288',
                'longitude' => '76.69856',
                'createdAt' => DateTime::createFromFormat('Y-m-d H:i:s', '2015-03-05 15:38:38'),
                'updatedAt' => DateTime::createFromFormat('Y-m-d H:i:s', '2015-12-27 17:25:58'),
            ],
            [
                'id' => 700,
                'map' => $this->maps[1],
                'markerType' => $this->markersTypes[1],
                'name' => '{0, 0}',
                'latitude' => '0.0',
                'longitude' => '0.0',
                'routesStart' => new ArrayCollection(),
                'routesEnd' => new ArrayCollection(),
                'createdAt' => new DateTime(),
                'updatedAt' => new DateTime(),
            ],
            [
                'id' => 701,
                'map' => $this->maps[1],
                'markerType' => $this->markersTypes[1],
                'name' => '{0, 10}',
                'latitude' => '0.0',
                'longitude' => '10.0',
                'routesStart' => new ArrayCollection(),
                'routesEnd' => new ArrayCollection(),
                'createdAt' => new DateTime(),
                'updatedAt' => new DateTime(),
            ],
            [
                'id' => 702,
                'map' => $this->maps[1],
                'markerType' => $this->markersTypes[1],
                'name' => '{10, 10}',
                'latitude' => '10.0',
                'longitude' => '10.0',
                'routesStart' => new ArrayCollection(),
                'routesEnd' => new ArrayCollection(),
                'createdAt' => new DateTime(),
                'updatedAt' => new DateTime(),
            ],
            [
                'id' => 703,
                'map' => $this->maps[1],
                'markerType' => $this->markersTypes[1],
                'name' => '{10, 0}',
                'latitude' => '10.0',
                'longitude' => '0.0',
                'routesStart' => new ArrayCollection(),
                'routesEnd' => new ArrayCollection(),
                'createdAt' => new DateTime(),
                'updatedAt' => new DateTime(),
            ],
        ];
    }
}
