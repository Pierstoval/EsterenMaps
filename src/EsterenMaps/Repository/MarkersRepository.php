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

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EsterenMaps\Entity\Map;
use EsterenMaps\Entity\Marker;

/**
 * MarkersRepository.
 */
class MarkersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Marker::class);
    }

    public function findForApiByMap($mapId)
    {
        $query = $this->createQueryBuilder('marker')
            ->select('
                marker.id,
                marker.name,
                marker.description,
                marker.latitude,
                marker.longitude,
                isNoteFrom.id as is_note_from,
                markerType.id as marker_type,
                markerFaction.id as faction
            ')
            ->leftJoin('marker.map', 'map')
            ->leftJoin('marker.isNoteFrom', 'isNoteFrom')
            ->leftJoin('marker.markerType', 'markerType')
            ->leftJoin('marker.faction', 'markerFaction')
            ->where('map.id = :id')
            ->setParameter('id', $mapId)
            ->indexBy('marker', 'marker.id')
            ->getQuery()
        ;

        $query->enableResultCache(3600, "esterenmaps_api_map_{$mapId}\\_markers");

        return $query->getArrayResult();
    }

    /**
     * @return array<Marker>
     */
    public function findForMap(Map $map): array
    {
        return $this->createQueryBuilder('markers')
            ->where('markers.map = :map')
            ->setParameter('map', $map)
            ->getQuery()
            ->getResult()
        ;
    }
}
