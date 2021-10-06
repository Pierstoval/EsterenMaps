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

namespace Tests\EsterenMaps\Services;

use Doctrine\ORM\EntityManagerInterface;
use EsterenMaps\Entity\Map;
use EsterenMaps\Entity\Marker;
use EsterenMaps\Entity\TransportType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\GetHttpClientTestTrait;

class DirectionsManagerTest extends WebTestCase
{
    use GetHttpClientTestTrait;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    /**
     * @dataProvider provideWorkingDirections
     * @group functional
     */
    public function testWorkingDirections(array $expectedData, string $map, int $from, int $to, string $transport = null): void
    {
        $client = $this->getHttpClient('maps.esteren.docker');
        $this->loginAsUser($client);

        $em = static::$container->get(EntityManagerInterface::class);

        /** @var Map $map */
        $map = $em->getRepository(Map::class)->findOneBy(['nameSlug' => $map]);

        $markersRepo = $em->getRepository(Marker::class);

        /** @var Marker $from */
        $from = $markersRepo->find($from);

        /** @var Marker $to */
        $to = $markersRepo->find($to);

        $queryString = 'hours_per_day=7';

        if (null !== $transport) {
            $transport = $em->getRepository(TransportType::class)->findOneBy(['slug' => $transport]);
            $queryString .= '&transport='.$transport->getId();
        }

        $client->request('GET', \sprintf('/fr/api/maps/directions/%d/%d/%d?%s', $map->getId(), $from->getId(), $to->getId(), $queryString));

        static::assertResponseStatusCodeSame(200);
        $dirs = \json_decode($client->getResponse()->getContent(), true);
        static::assertIsArray($dirs, \json_last_error().'#'.\json_last_error_msg());

        foreach ($expectedData as $key => $expectedValue) {
            static::assertArrayHasKey($key, $dirs);
            if (\array_key_exists($key, $dirs)) {
                static::assertSame($expectedValue, $dirs[$key], 'Json response key "'.$key.'" has invalid value.');
            }
        }
    }

    /**
     * Syntax:
     * > Expected output values (will check only these ones, if there are others, we don't check it).
     * > Map slug
     * > FROM Marker name
     * > TO marker name
     * > WITH Transport slug (can be null).
     *
     * @return array[]
     */
    public function provideWorkingDirections()
    {
        return [
            // Test from bottom left to top right with no transport (similar to "default")
            0 => [
                [
                    'found' => true,
                    'from_cache' => false,
                    'number_of_steps' => 0,
                    'total_distance' => 50, // Should be route 702, "long way and no stop"
                    'duration_raw' => null,
                    'duration_real' => [
                        'days' => null,
                        'hours' => null,
                    ],
                ],
                'tri-kazel',
                700, // {0, 0}
                701, // {0, 10}
                null,
            ],
            /*
            // Test from bottom left to top right with "default" transport
            1 => [
                [
                    'found'           => true,
                    'from_cache'      => false,
                    'number_of_steps' => 16,
                ],
                'tri-kazel',
                76, // Pointe de Hòb
                40, // Col de Gaos-Bodhar
                'transport-par-defaut',
            ],
            // Test from bottom left to top right with ground transport
            2 => [
                [
                    'found'           => true,
                    'from_cache'      => false,
                    'number_of_steps' => 18,
                ],
                'tri-kazel',
                76, // Pointe de Hòb
                40, // Col de Gaos-Bodhar
                'chariot',
            ],
            // Test small ship transport with only routes in the sea
            3 => [
                [
                    'found'           => true,
                    'from_cache'      => false,
                    'number_of_steps' => 2,
                ],
                'tri-kazel',
                7,  // Tuaille
                72, // Seòl
                'coracle',
            ],
            // Test the simple routes set up for test only
            // First, with no transport
            4 => [
                [
                    'found'           => true,
                    'from_cache'      => false,
                    'number_of_steps' => 0,
                    'total_distance'  => 70.710678118,
                    'duration_raw'    => null,
                    'duration_real'   => [
                        'days' => null,
                        'hours' => null,
                    ],
                    'bounds'          => [
                        'northEast' => [
                            'lat' => 10,
                            'lng' => 10,
                        ],
                        'southWest' => [
                            'lat' => 0,
                            'lng' => 0,
                        ],
                    ],
                ],
                'tri-kazel',
                700, // {0, 0}
                702, // {10, 10}
                null,
            ],
            // With water transport, should be exactly the same as with no transport, but with duration explained
            5 => [
                [
                    'found'           => true,
                    'from_cache'      => false,
                    'number_of_steps' => 0,
                    'total_distance'  => 70.710678118,
                    'duration_raw'    => 'P0Y0M0DT8H51M0S',
                    'duration_real'   => [
                        'days' => 1,
                        'hours' => 1.84,
                    ],
                    'bounds'          => [
                        'northEast' => [
                            'lat' => 10,
                            'lng' => 10,
                        ],
                        'southWest' => [
                            'lat' => 0,
                            'lng' => 0,
                        ],
                    ],
                ],
                'tri-kazel',
                700, // {0, 0}
                702, // {10, 10}
                'koggen',
            ],
            // With water transport that should be slower
            6 => [
                [
                    'found'           => true,
                    'from_cache'      => false,
                    'number_of_steps' => 0,
                    'total_distance'  => 70.710678118,
                    'duration_raw'    => 'P0Y0M0DT17H41M0S',
                    'duration_real'   => [
                        'days' => 2,
                        'hours' => 3.68,
                    ],
                    'bounds'          => [
                        'northEast' => [
                            'lat' => 10,
                            'lng' => 10,
                        ],
                        'southWest' => [
                            'lat' => 0,
                            'lng' => 0,
                        ],
                    ],
                ],
                'tri-kazel',
                700, // {0, 0}
                702, // {10, 10}
                'coracle',
            ],
            // With ground transport that should be way slower
            7 => [
                [
                    'found'           => true,
                    'from_cache'      => false,
                    'number_of_steps' => 0,
                    'total_distance'  => 300,
                    'duration_raw'    => 'P0Y0M1DT13H30M0S',
                    'duration_real'   => [
                        'days' => 5,
                        'hours' => 2.5,
                    ],
                    'bounds'          => [
                        'northEast' => [
                            'lat' => 20,
                            'lng' => 10,
                        ],
                        'southWest' => [
                            'lat' => 0,
                            'lng' => -10,
                        ],
                    ],
                ],
                'tri-kazel',
                700, // {0, 0}
                702, // {10, 10}
                'chariot',
            ],
            // With ground transport and with one step
            8 => [
                [
                    'found'           => true,
                    'from_cache'      => false,
                    'number_of_steps' => 1,
                    'total_distance'  => 350,
                    'duration_raw'    => 'P0Y0M1DT19H45M0S',
                    'duration_real'   => [
                        'days' => 6,
                        'hours' => 1.75,
                    ],
                    'bounds'          => [
                        'northEast' => [
                            'lat' => 20,
                            'lng' => 10,
                        ],
                        'southWest' => [
                            'lat' => 0,
                            'lng' => -10,
                        ],
                    ],
                ],
                'tri-kazel',
                701, // {0, 10}
                702, // {10, 10}
                'chariot',
            ],
            // Should not be found, as no route match (test route to esteren maps route, no route between)
            9 => [
                [
                    'found'           => false,
                    'from_cache'      => false,
                    'number_of_steps' => 0,
                    'total_distance'  => null,
                    'duration_raw'    => null,
                    'duration_real'   => [
                        'days' => null,
                        'hours' => null,
                    ],
                ],
                'tri-kazel',
                700, // {0, 0}
                76,  // Pointe de Hòb
                null,
            ],
            */
        ];
    }
}
