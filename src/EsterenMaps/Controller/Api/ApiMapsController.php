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

use EsterenMaps\Api\MapApi;
use Main\DependencyInjection\PublicService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiMapsController implements PublicService
{
    private MapApi $api;

    public function __construct(
        MapApi $api
    ) {
        $this->api = $api;
    }

    /**
     * @Route(
     *     "/api/maps/{id}",
     *     name="maps_api_maps_get",
     *     requirements={"id" = "\d+"},
     *     methods={"GET"}
     * )
     */
    public function getMap(int $id): JsonResponse
    {
        $response = new JsonResponse($this->api->getMap($id));

        // Fixes issues with floats converted to string when array is encoded.
        $response->setEncodingOptions($response::DEFAULT_ENCODING_OPTIONS | \JSON_PRESERVE_ZERO_FRACTION | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);

        return $response;
    }
}
