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

namespace EsterenMaps\Api;

use EsterenMaps\Repository\FactionsRepository;
use EsterenMaps\Repository\MapsRepository;
use EsterenMaps\Repository\MarkersRepository;
use EsterenMaps\Repository\RoutesTypesRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RoutePayloadSanitizer implements MapElementPayloadSanitizerInterface
{
    private TokenStorageInterface $tokenStorage;
    private RoutesTypesRepository $routesTypesRepository;
    private MapsRepository $mapsRepository;
    private MarkersRepository $markersRepository;
    private FactionsRepository $factionsRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        RoutesTypesRepository $routesTypesRepository,
        MapsRepository $mapsRepository,
        MarkersRepository $markersRepository,
        FactionsRepository $factionsRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->routesTypesRepository = $routesTypesRepository;
        $this->mapsRepository = $mapsRepository;
        $this->markersRepository = $markersRepository;
        $this->factionsRepository = $factionsRepository;
    }

    public function sanitizeRequestData(array $data): array
    {
        if (isset($data['map'])) {
            $data['map'] = $this->mapsRepository->find($data['map']['id'] ?? $data['map']);
        }

        if (isset($data['faction'])) {
            $data['faction'] = $this->factionsRepository->find($data['faction']['id'] ?? $data['faction']);
        }

        if (isset($data['routeType'])) {
            $data['routeType'] = $this->routesTypesRepository->find($data['routeType']['id'] ?? $data['routeType']);
        }

        if (isset($data['markerStart'])) {
            $data['markerStart'] = $this->markersRepository->find($data['markerStart']['id'] ?? $data['markerStart']);
        }

        if (isset($data['markerEnd'])) {
            $data['markerEnd'] = $this->markersRepository->find($data['markerEnd']['id'] ?? $data['markerEnd']);
        }

        if (\array_key_exists('isNote', $data)) {
            if ($data['isNote']) {
                $data['isNoteFrom'] = $this->tokenStorage->getToken()->getUser();
            }
            unset($data['isNote']);
        }

        return $data;
    }
}
