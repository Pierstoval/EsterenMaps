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

namespace Admin\CustomController;

use Admin\Controller\AdminController;
use Doctrine\ORM\QueryBuilder;

class BaseMapAdminController extends AdminController
{
    protected function createMarkersListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null): QueryBuilder
    {
        $qb = $this->createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);

        return $qb->leftJoin('entity.markerType', 'type')->addSelect('type');
    }

    protected function createMarkersTypesListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null): QueryBuilder
    {
        $qb = $this->createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);

        return $qb->leftJoin('entity.markers', 'markers')->addSelect('markers');
    }

    protected function createRoutesListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null): QueryBuilder
    {
        $qb = $this->createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);

        return $qb
            ->leftJoin('entity.routeType', 'type')->addSelect('type')
            ->leftJoin('entity.markerStart', 'markerStart')->addSelect('markerStart')
            ->leftJoin('entity.markerEnd', 'markerEnd')->addSelect('markerEnd')
        ;
    }

    protected function createRoutesTypesListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null): QueryBuilder
    {
        $qb = $this->createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);

        return $qb->leftJoin('entity.routes', 'routes')->addSelect('routes');
    }

    protected function createZonesListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null): QueryBuilder
    {
        $qb = $this->createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);

        return $qb
            ->leftJoin('entity.zoneType', 'type')->addSelect('type')
            ->leftJoin('entity.map', 'map')->addSelect('map')
            ->leftJoin('entity.faction', 'faction')->addSelect('faction')
        ;
    }

    protected function createZonesTypesListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null): QueryBuilder
    {
        $qb = $this->createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);

        return $qb->leftJoin('entity.zones', 'zones')->addSelect('zones');
    }

    protected function createFactionsListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null): QueryBuilder
    {
        $qb = $this->createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);

        return $this->joinBooks($qb);
    }

    protected function joinBooks(QueryBuilder $qb): QueryBuilder
    {
        $qb->leftJoin('entity.book', 'book')->addSelect('book');

        return $qb;
    }
}
