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
use EsterenMaps\Repository\ZonesTypesRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ZonePayloadSanitizer implements MapElementPayloadSanitizerInterface
{
    private TokenStorageInterface $tokenStorage;
    private ZonesTypesRepository $zonesTypesRepository;
    private MapsRepository $mapsRepository;
    private FactionsRepository $factionsRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ZonesTypesRepository $zonesTypesRepository,
        MapsRepository $mapsRepository,
        FactionsRepository $factionsRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->zonesTypesRepository = $zonesTypesRepository;
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

        if (isset($data['zoneType'])) {
            $data['zoneType'] = $this->zonesTypesRepository->find($data['zoneType']);
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
