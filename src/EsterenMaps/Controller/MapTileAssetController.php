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

namespace EsterenMaps\Controller;

use Main\DependencyInjection\PublicService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class MapTileAssetController implements PublicService
{
    private string $publicDir;

    public function __construct(string $publicDir)
    {
        $this->publicDir = $publicDir;
    }

    /**
     * @Route("/maps_tiles/{id}/{z}/{x}/{y}.jpg", name="esterenmaps_tile_asset", methods={"GET"})
     */
    public function mapTilesAssetsAction(): Response
    {
        $file = $this->publicDir.'/maps_tiles/empty.jpg';
        $size = \filesize($file);

        // Acts as a fallback to map tiles that do not exist
        return new StreamedResponse(
            static function () use ($file): void {
                echo \file_get_contents($file);
            },
            200,
            [
                'Content-Length' => $size,
                'Content-Type' => 'image/jpeg',
            ]
        );
    }
}
