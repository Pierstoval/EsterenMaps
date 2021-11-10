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

namespace EsterenMaps\Map;

use EsterenMaps\Entity\Map;
use EsterenMaps\Security\ApiMapElementVoter;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use User\Entity\User;

class MapOptions
{
    private int $tileSize;
    private AuthorizationCheckerInterface $authChecker;
    private UrlGeneratorInterface $router;
    private Packages $packages;
    private RequestStack $requestStack;

    public function __construct(
        int $tileSize,
        AuthorizationCheckerInterface $authChecker,
        UrlGeneratorInterface $router,
        Packages $packages,
        RequestStack $requestStack
    ) {
        $this->tileSize = $tileSize;
        $this->authChecker = $authChecker;
        $this->router = $router;
        $this->packages = $packages;
        $this->requestStack = $requestStack;
    }

    public function getMapViewOptions(Map $map, User $user): string
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();

        $mapOptions = $this->getDefaultOptions($map, $request);

        $mapOptions['canAddNotes'] = $this->authChecker->isGranted('SUBSCRIBED_TO_MAPS') && $this->authChecker->isGranted(ApiMapElementVoter::CAN_ADD_NOTES);
        $mapOptions['visitor'] = $user->getId();

        return \json_encode($mapOptions, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }

    private function getDefaultOptions(Map $map, Request $request): array
    {
        $mapApiUrl = $this->router->generate(
            'maps_api_maps_get',
            ['id' => $map->getId(), 'host' => $request->getHost()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $directionsUrl = $this->router->generate('esterenmaps_directions', [
            'id' => $map->getId(),
            'from' => '9999',
            'to' => '8888',
            'host' => $request->getHost(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $directionsUrl = \strtr($directionsUrl, [
            '9999' => '{from}',
            '8888' => '{to}',
        ]);

        $tilesUrl = $request->getSchemeAndHttpHost().$this->packages->getUrl('maps_tiles/'.$map->getId().'/{z}/{y}/{x}.jpg');

        $endpoint = $request->getSchemeAndHttpHost().'/'.$request->getLocale().'/';

        $mapOptions = [
            'id' => $map->getId(),
            'apiUrls' => [
                'map' => $mapApiUrl,
                'directions' => $directionsUrl,
                'tiles' => $tilesUrl,
                'endpoint' => $endpoint,
            ],
            'LeafletMapBaseOptions' => [
                'zoom' => $map->getStartZoom(),
                'maxZoom' => $map->getMaxZoom(),
            ],
            'LeafletLayerBaseOptions' => [
                'maxZoom' => $map->getMaxZoom(),
                'maxNativeZoom' => $map->getMaxZoom(),
                'tileSize' => $this->tileSize,
            ],
        ];

        if ($map->getArrayBounds()) {
            $mapOptions['LeafletMapBaseOptions']['maxBounds'] = $map->getArrayBounds();
        }

        return $mapOptions;
    }
}
