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

namespace Admin\CustomController;

use EsterenMaps\Entity\TransportModifier;
use EsterenMaps\Entity\TransportType;
use EsterenMaps\Repository\RoutesTypesRepository;
use Symfony\Component\Form\FormBuilder;

class TransportTypesController extends BaseMapAdminController
{
    private $routesTypesRepository;

    public function __construct(RoutesTypesRepository $routesTypesRepository)
    {
        $this->routesTypesRepository = $routesTypesRepository;
    }

    protected function createTransportTypesEntityFormBuilder(TransportType $entity, string $view): FormBuilder
    {
        // Get IDs in the entity and try to retrieve non-existing transport ids.
        $routesTypesIds = \array_reduce(
            $entity->getTransportsModifiers()->toArray(),
            static function (array $carry, TransportModifier $routeTransport) {
                $carry[] = $routeTransport->getRouteType()->getId();

                return $carry;
            },
            []
        );

        $missingRoutesTypes = $this->routesTypesRepository->findNotInIds($routesTypesIds);

        foreach ($missingRoutesTypes as $routeType) {
            $entity->addTransportsModifier(
                (new TransportModifier())
                    ->setTransportType($entity)
                    ->setRouteType($routeType)
            );
        }

        return $this->createEntityFormBuilder($entity, $view);
    }
}
