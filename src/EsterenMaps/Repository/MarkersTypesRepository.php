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
use EsterenMaps\Entity\MarkerType;

class MarkersTypesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MarkerType::class);
    }

    public function findForApi()
    {
        $query = $this->createQueryBuilder('marker_type')
            ->select('
                marker_type.id,
                marker_type.name,
                marker_type.description,
                marker_type.icon,
                marker_type.iconWidth as icon_width,
                marker_type.iconHeight as icon_height,
                marker_type.iconCenterX as icon_center_x,
                marker_type.iconCenterY as icon_center_y
            ')
            ->indexBy('marker_type', 'marker_type.id')
            ->getQuery()
        ;

        $query->enableResultCache(3600, 'esterenmaps_api_markers_types');

        return $query->getArrayResult();
    }
}
