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

namespace Admin\CustomController\EsterenMaps;

use Admin\CustomController\BaseDTOControllerTrait;
use Admin\DTO\EasyAdminDTOInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use EsterenMaps\DTO\MapAdminDTO;
use EsterenMaps\Entity\Map;

class AdminMapsController extends EasyAdminController
{
    use BaseDTOControllerTrait;

    protected function getDTOClass(): string
    {
        return MapAdminDTO::class;
    }

    /**
     * @param MapAdminDTO $dto
     */
    protected function createEntityFromDTO(EasyAdminDTOInterface $dto): object
    {
        return Map::fromAdmin($dto);
    }

    /**
     * @param Map         $entity
     * @param MapAdminDTO $dto
     */
    protected function updateEntityWithDTO(object $entity, EasyAdminDTOInterface $dto): object
    {
        $entity->updateFromAdmin($dto);

        $this->translateEntityFieldsFromDTO($entity, $dto);

        return $entity;
    }
}
