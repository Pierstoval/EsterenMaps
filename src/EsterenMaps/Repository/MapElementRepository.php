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

namespace EsterenMaps\Repository;

use Doctrine\ORM\EntityManagerInterface;
use EsterenMaps\Entity\Marker;
use EsterenMaps\Entity\Route;
use EsterenMaps\Entity\Zone;
use User\Entity\User;

class MapElementRepository
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function numberOfElementsForUser(User $user): int
    {
        $markerClass = Marker::class;
        $routeClass = Route::class;
        $zoneClass = Zone::class;
        $userClass = User::class;

        $query = $this->em->createQuery(
            <<<DQL
            SELECT
                COUNT(u.id) as users,
                (
                    SELECT COUNT(marker.id)
                    FROM {$markerClass} as marker
                    WHERE marker.isNoteFrom = :user
                ) as markers_count,
                (
                    SELECT COUNT(route.id)
                    FROM {$routeClass} as route
                    WHERE route.isNoteFrom = :user
                ) as routes_count,
                (
                    SELECT COUNT(zone.id)
                    FROM {$zoneClass} as zone
                    WHERE zone.isNoteFrom = :user
                ) as zones_count
            FROM {$userClass} u
            WHERE u = :user
            DQL
        )
            ->setParameter('user', $user)
        ;

        $result = $query->getSingleResult();

        return
            $result['markers_count']
            + $result['routes_count']
            + $result['zones_count']
        ;
    }
}
