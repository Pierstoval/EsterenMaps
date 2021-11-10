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

namespace Admin\CustomController\EsterenMaps;

use Admin\Controller\AdminController;
use Admin\CustomController\BaseDTOControllerTrait;
use Admin\DTO\EasyAdminDTOInterface;
use EsterenMaps\DTO\MarkerAdminDTO;
use EsterenMaps\Entity\Marker;

class AdminMarkerController extends AdminController
{
    use BaseDTOControllerTrait;

    private string $defaultLocale;

    public function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    protected function getDTOClass(): string
    {
        return MarkerAdminDTO::class;
    }

    /**
     * @param MarkerAdminDTO $dto
     */
    protected function createEntityFromDTO(EasyAdminDTOInterface $dto): object
    {
        return Marker::fromAdmin($dto, $this->defaultLocale);
    }

    /**
     * @param Marker         $entity
     * @param MarkerAdminDTO $dto
     */
    protected function updateEntityWithDTO(object $entity, EasyAdminDTOInterface $dto): object
    {
        $entity->updateFromAdmin($dto, $this->defaultLocale);

        $this->translateEntityFieldsFromDTO($entity, $dto);

        return $entity;
    }
}
