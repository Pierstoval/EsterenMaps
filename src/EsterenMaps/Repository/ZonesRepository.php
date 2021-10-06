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

namespace EsterenMaps\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EsterenMaps\Entity\Zone;

class ZonesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zone::class);
    }

    public function findForApiByMap($mapId)
    {
        $query = $this->createQueryBuilder('zone')
            ->select('
                zone.id,
                zone.name,
                zone.description,
                zone.coordinates,
                isNoteFrom.id as is_note_from,
                zoneFaction.id as faction,
                zoneType.id as zone_type
            ')
            ->leftJoin('zone.map', 'map')
            ->leftJoin('zone.isNoteFrom', 'isNoteFrom')
            ->leftJoin('zone.faction', 'zoneFaction')
            ->leftJoin('zone.zoneType', 'zoneType')
            ->indexBy('zone', 'zone.id')
            ->where('map.id = :id')
            ->setParameter('id', $mapId)
            ->getQuery()
        ;

        $query->enableResultCache(3600, "esterenmaps_api_map_{$mapId}\\_zones");

        return $query->getArrayResult();
    }
}
