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

namespace EsterenMaps\Api;

use EsterenMaps\Repository\FactionsRepository;
use EsterenMaps\Repository\MapsRepository;
use EsterenMaps\Repository\MarkersTypesRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MarkerPayloadSanitizer implements MapElementPayloadSanitizerInterface
{
    private MarkersTypesRepository $markersTypesRepository;
    private MapsRepository $mapsRepository;
    private FactionsRepository $factionsRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        MarkersTypesRepository $markersTypesRepository,
        MapsRepository $mapsRepository,
        FactionsRepository $factionsRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->markersTypesRepository = $markersTypesRepository;
        $this->mapsRepository = $mapsRepository;
        $this->factionsRepository = $factionsRepository;
    }

    public function sanitizeRequestData(array $data): array
    {
        if (isset($data['map'])) {
            $data['map'] = $this->mapsRepository->find($data['map']);
        }

        if (isset($data['faction'])) {
            $data['faction'] = $this->factionsRepository->find($data['faction']);
        }

        if (isset($data['markerType'])) {
            $data['markerType'] = $this->markersTypesRepository->find($data['markerType']);
        }

        if (isset($data['altitude'])) {
            $data['altitude'] = (float) $data['altitude'];
        }

        if (isset($data['latitude'])) {
            $data['latitude'] = (float) $data['latitude'];
        }

        if (isset($data['longitude'])) {
            $data['longitude'] = (float) $data['longitude'];
        }

        if (isset($data['isNote'])) {
            $data['isNoteFrom'] = $this->tokenStorage->getToken()->getUser();
            unset($data['isNote']);
        }

        return $data;
    }
}
