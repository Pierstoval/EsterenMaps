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
use EsterenMaps\DTO\MarkerTypeAdminDTO;
use EsterenMaps\Entity\MarkerType;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminMarkerTypeController extends AdminController
{
    use BaseDTOControllerTrait;

    private string $defaultLocale;
    private string $publicDir;
    private SluggerInterface $slugger;

    public function __construct(string $defaultLocale, string $publicDir, SluggerInterface $slugger)
    {
        $this->defaultLocale = $defaultLocale;
        $this->publicDir = $publicDir;
        $this->slugger = $slugger;
    }

    protected function getDTOClass(): string
    {
        return MarkerTypeAdminDTO::class;
    }

    /**
     * @param MarkerTypeAdminDTO $dto
     */
    protected function createEntityFromDTO(EasyAdminDTOInterface $dto): object
    {
        $this->uploadFile($dto);

        return MarkerType::fromAdmin($dto, $this->defaultLocale);
    }

    /**
     * @param MarkerType         $entity
     * @param MarkerTypeAdminDTO $dto
     */
    protected function updateEntityWithDTO(object $entity, EasyAdminDTOInterface $dto): object
    {
        $this->uploadFile($dto);

        $entity->updateFromAdmin($dto, $this->defaultLocale);

        $this->translateEntityFieldsFromDTO($entity, $dto);

        return $entity;
    }

    private function uploadFile(MarkerTypeAdminDTO $dto): void
    {
        if (!$dto->getIconFile()) {
            return;
        }

        $dto->uploadedFileName = (string) $this->slugger->slug(
            $dto->translatedNames[$this->defaultLocale]
            .'_'
            .$dto->getIconFile()->getClientOriginalName()
        );

        $dto->getIconFile()->move(
            \rtrim($this->publicDir, '/').'/'.\ltrim(MarkerType::PUBLIC_PATH_BASE, '/'),
            $dto->uploadedFileName
        );
    }
}
