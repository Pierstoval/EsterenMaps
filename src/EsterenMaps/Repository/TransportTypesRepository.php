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
use EsterenMaps\Entity\TransportType;

/**
 * @method null|TransportType findOneBy(array $criteria, array $orderBy = null)
 */
class TransportTypesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransportType::class);
    }

    public function findForApi()
    {
        $query = $this->createQueryBuilder('transport_type')
            ->select('
                transport_type.id,
                transport_type.name,
                transport_type.slug,
                transport_type.description,
                transport_type.speed
            ')
            ->indexBy('transport_type', 'transport_type.id')
            ->getQuery()
        ;

        $query->enableResultCache(3600, 'esterenmaps_api_transports_types');

        return $query->getArrayResult();
    }
}
