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
use EsterenMaps\Entity\Faction;

/**
 * @method Faction[]    findAll()
 * @method Faction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Faction findOneBy(array $criteria, array $orderBy = null)
 * @method null|Faction find($id, $lockMode = null, $lockVersion = null)
 */
class FactionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faction::class);
    }

    public function findForApi()
    {
        $query = $this->createQueryBuilder('faction')
            ->select('
                faction.id,
                faction.name,
                faction.description
            ')
            ->indexBy('faction', 'faction.id')
            ->getQuery()
        ;

        $query->enableResultCache(3600, 'esterenmaps_api_factions');

        return $query->getArrayResult();
    }
}
