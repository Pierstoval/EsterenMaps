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

use Admin\Controller\AdminController;
use Admin\CustomController\BaseDTOControllerTrait;
use Admin\DTO\EasyAdminDTOInterface;
use EsterenMaps\DTO\FactionAdminDTO;
use EsterenMaps\Entity\Faction;

class AdminFactionController extends AdminController
{
    use BaseDTOControllerTrait;

    protected function getDTOClass(): string
    {
        return FactionAdminDTO::class;
    }

    /**
     * @param FactionAdminDTO $dto
     */
    protected function createEntityFromDTO(EasyAdminDTOInterface $dto): object
    {
        return Faction::fromAdmin($dto);
    }

    /**
     * @param Faction         $entity
     * @param FactionAdminDTO $dto
     */
    protected function updateEntityWithDTO(object $entity, EasyAdminDTOInterface $dto): object
    {
        $entity->updateFromAdmin($dto);

        $this->translateEntityFieldsFromDTO($entity, $dto);

        return $entity;
    }
}
