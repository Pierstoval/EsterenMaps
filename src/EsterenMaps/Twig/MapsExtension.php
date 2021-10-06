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

namespace EsterenMaps\Twig;

use EsterenMaps\Map\MapOptions;
use EsterenMaps\Repository\MapsRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MapsExtension extends AbstractExtension
{
    private MapsRepository $repository;
    private MapOptions $mapOptions;

    public function __construct(MapsRepository $repository, MapOptions $mapOptions)
    {
        $this->repository = $repository;
        $this->mapOptions = $mapOptions;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_menu_maps', [$this->repository, 'findForMenu']),
            new TwigFunction('map_view_options', [$this->mapOptions, 'getMapViewOptions'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('urldecode', 'urldecode'),
        ];
    }
}
