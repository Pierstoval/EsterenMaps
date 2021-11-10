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

use DateTime;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use EsterenMaps\Entity\Route;
use Orbitale\Component\ArrayFixture\ArrayFixture;

class RoutesFixtures extends ArrayFixture implements ORMFixtureInterface, DependentFixtureInterface
{
    private $maps;
    private $factions;
    private $markers;
    private $routesTypes;

    public function getDependencies()
    {
        return [
            MapsFixtures::class,
            RoutesTypesFixtures::class,
            FactionsFixtures::class,
            MarkersFixtures::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityClass(): string
    {
        return Route::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getReferencePrefix(): ?string
    {
        return 'esterenmaps-routes-';
    }

    /**
     * {@inheritdoc}
     */
    protected function clearEntityManagerOnFlush(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjects(): array
    {
        $this->maps = [
            1 => $this->getReference('esterenmaps-maps-tri-kazel'), // Tri-Kazel
        ];

        $this->routesTypes = [
            1 => $this->getReference('esterenmaps-routestypes-1'), // Chemin
            2 => $this->getReference('esterenmaps-routestypes-2'), // Route
            3 => $this->getReference('esterenmaps-routestypes-3'), // Sentier de loup
            4 => $this->getReference('esterenmaps-routestypes-4'), // Liaison maritime
            5 => $this->getReference('esterenmaps-routestypes-5'), // Voie fluviale
        ];

        $this->factions = [
            1 => $this->getReference('esterenmaps-factions-Faction Test'), // Faction test
        ];

        $this->markers = [];
        for ($i = 1; $i <= 177; ++$i) {
            if ($this->hasReference('esterenmaps-markers-'.$i)) {
                $this->markers[$i] = $this->getReference('esterenmaps-markers-'.$i);
            }
        }

        $id = 699;

        return [
            [
                'id' => ++$id,
                'markerStart' => $this->getReference('esterenmaps-markers-700'),
                'markerEnd' => $this->getReference('esterenmaps-markers-701'),
                'map' => $this->maps[1],
                'routeType' => $this->routesTypes[1],
                'name' => 'From 0,0 to 0,10',
                'coordinates' => '[{"lat":0,"lng":0},{"lat":0,"lng":10}]',
                'distance' => 10.0,
                'createdAt' => new DateTime(),
                'updatedAt' => new DateTime(),
            ],
            [
                'id' => ++$id,
                'markerStart' => $this->getReference('esterenmaps-markers-700'),
                'markerEnd' => $this->getReference('esterenmaps-markers-703'),
                'map' => $this->maps[1],
                'routeType' => $this->routesTypes[1],
                'name' => 'From 0,0 to 10,0',
                'coordinates' => '[{"lat":0,"lng":0},{"lat":10,"lng":0}]',
                'distance' => 10.0,
                'createdAt' => new DateTime(),
                'updatedAt' => new DateTime(),
            ],
            [
                'id' => ++$id,
                'markerStart' => $this->getReference('esterenmaps-markers-700'),
                'markerEnd' => $this->getReference('esterenmaps-markers-702'),
                'map' => $this->maps[1],
                'routeType' => $this->routesTypes[1],
                'name' => 'From 0,0 to 10,10 (long way, no stop)',
                'coordinates' => '[{"lat":0,"lng":0},{"lat":0,"lng":-10},{"lat":20,"lng":-10},{"lat":20,"lng":10},{"lat":10,"lng":10}]',
                'distance' => 50.0,
                'createdAt' => new DateTime(),
                'updatedAt' => new DateTime(),
            ],
            [
                'id' => ++$id,
                'markerStart' => $this->getReference('esterenmaps-markers-700'),
                'markerEnd' => $this->getReference('esterenmaps-markers-702'),
                'map' => $this->maps[1],
                'routeType' => $this->routesTypes[5],
                'name' => 'From 0,0 to 10,10 (short way but only water)',
                'coordinates' => '[{"lat":0,"lng":0},{"lat":10,"lng":10}]',
                'distance' => 14.14213562,
                'createdAt' => new DateTime(),
                'updatedAt' => new DateTime(),
            ],
        ];
    }
}
