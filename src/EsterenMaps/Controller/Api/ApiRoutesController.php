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

namespace EsterenMaps\Controller\Api;

use EsterenMaps\Api\RoutePayloadSanitizer;
use EsterenMaps\DTO\Api\RouteDTO;
use EsterenMaps\Entity\Route as RouteEntity;
use Main\DependencyInjection\PublicService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiRoutesController implements PublicService
{
    use ApiMapElementTrait;

    /** @required */
    public function setMapElementSanitizer(RoutePayloadSanitizer $mapElementSanitizer): void
    {
        $this->mapElementSanitizer = $mapElementSanitizer;
    }

    /**
     * @Route(
     *     "/routes",
     *     name="maps_api_routes_create",
     *     methods={"POST"},
     *     defaults={"_format" = "json"}
     * )
     */
    public function create(Request $request): Response
    {
        return $this->createAction($request, RouteDTO::class, RouteEntity::class);
    }

    /**
     * @Route(
     *     "/routes/{id}",
     *     name="maps_api_routes_update",
     *     methods={"POST"},
     *     defaults={"_format" = "json"}
     * )
     */
    public function update(RouteEntity $mapElement, Request $request): Response
    {
        return $this->updateAction($request, RouteDTO::class, $mapElement);
    }

    /**
     * @Route(
     *     "/routes/{id}",
     *     name="maps_api_routes_delete",
     *     methods={"DELETE"},
     *     defaults={"_format" = "json"}
     * )
     */
    public function delete(RouteEntity $mapElement): Response
    {
        return $this->deleteAction($mapElement);
    }
}
