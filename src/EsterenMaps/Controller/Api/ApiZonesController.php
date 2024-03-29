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

use EsterenMaps\Api\ZonePayloadSanitizer;
use EsterenMaps\DTO\Api\ZoneDTO;
use EsterenMaps\Entity\Zone;
use Main\DependencyInjection\PublicService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiZonesController implements PublicService
{
    use ApiMapElementTrait;

    /** @required */
    public function setMapElementSanitizer(ZonePayloadSanitizer $mapElementSanitizer): void
    {
        $this->mapElementSanitizer = $mapElementSanitizer;
    }

    /**
     * @Route(
     *     "/zones",
     *     name="maps_api_zones_create",
     *     methods={"POST"},
     *     defaults={"_format" = "json"}
     * )
     */
    public function create(Request $request): Response
    {
        return $this->createAction($request, ZoneDTO::class, Zone::class);
    }

    /**
     * @Route(
     *     "/zones/{id}",
     *     name="maps_api_zones_update",
     *     methods={"POST"},
     *     defaults={"_format" = "json"}
     * )
     */
    public function update(Zone $mapElement, Request $request): Response
    {
        return $this->updateAction($request, ZoneDTO::class, $mapElement);
    }

    /**
     * @Route(
     *     "/zones/{id}",
     *     name="maps_api_zones_delete",
     *     methods={"DELETE"},
     *     defaults={"_format" = "json"}
     * )
     */
    public function delete(Zone $mapElement): Response
    {
        return $this->deleteAction($mapElement);
    }
}
