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

namespace EsterenMaps\Controller\Api;

use EsterenMaps\Api\MarkerPayloadSanitizer;
use EsterenMaps\DTO\Api\MarkerDTO;
use EsterenMaps\Entity\Marker;
use Main\DependencyInjection\PublicService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiMarkersController implements PublicService
{
    use ApiMapElementTrait;

    /** @required */
    public function setMapElementSanitizer(MarkerPayloadSanitizer $mapElementSanitizer): void
    {
        $this->mapElementSanitizer = $mapElementSanitizer;
    }

    /**
     * @Route(
     *     "/api/markers",
     *     name="maps_api_markers_create",
     *     methods={"POST"},
     *     defaults={"_format" = "json"}
     * )
     */
    public function create(Request $request): Response
    {
        return $this->createAction($request, MarkerDTO::class, Marker::class);
    }

    /**
     * @Route(
     *     "/api/markers/{id}",
     *     name="maps_api_markers_update",
     *     methods={"POST"},
     *     defaults={"_format" = "json"}
     * )
     */
    public function update(Marker $mapElement, Request $request): Response
    {
        return $this->updateAction($request, MarkerDTO::class, $mapElement);
    }

    /**
     * @Route(
     *     "/api/markers/{id}",
     *     name="maps_api_markers_delete",
     *     methods={"DELETE"},
     *     defaults={"_format" = "json"}
     * )
     */
    public function delete(Marker $mapElement): Response
    {
        return $this->deleteAction($mapElement);
    }
}
