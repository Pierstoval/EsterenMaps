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
use EsterenMaps\Entity\Map;

/**
 * @method null|Map    find($id, $lockMode = null, $lockVersion = null)
 * @method array|Map[] findAll()
 */
class MapsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Map::class);
    }

    /**
     * @return Map[]
     */
    public function findAllWithRoutes()
    {
        $qb = $this->createQueryBuilder('map');

        $qb
            ->leftJoin('map.routes', 'route')
            ->addSelect('route')
            ->leftJoin('route.markerStart', 'markerStart')
            ->addSelect('markerStart')
            ->leftJoin('route.markerEnd', 'markerEnd')
            ->addSelect('markerEnd')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array[]
     */
    public function findForMenu()
    {
        return $this->_em->createQueryBuilder()
            ->select('map.name, map.nameSlug')
            ->from($this->_entityName, 'map')
            ->getQuery()->getArrayResult()
        ;
    }

    public function findForApi($id)
    {
        $query = $this->createQueryBuilder('map')
            ->select('
                map.id,
                map.name,
                map.nameSlug as name_slug,
                map.image,
                map.description,
                map.maxZoom as max_zoom,
                map.startZoom as start_zoom,
                map.startX as start_x,
                map.startY as start_y,
                map.bounds,
                map.coordinatesRatio as coordinates_ratio
            ')
            ->where('map.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
        ;

        $query->enableResultCache(3600, "esterenmaps_api_map_{$id}");

        return $query->getOneOrNullResult($query::HYDRATE_ARRAY);
    }
}
