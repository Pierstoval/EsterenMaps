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

use Symfony\Component\Routing\Loader\Configurator\ImportConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RouteConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/*
 * Closures $_route and $_import are here to provide defaults.
 * These are necessary for prefixing and for locale.
 */
return static function (RoutingConfigurator $routes, Kernel $kernel) {
    $projectDir = \dirname(__DIR__);

    $environment = $kernel->getEnvironment();

    $_route = static function (string $name, string $path) use ($routes, $environment): RouteConfigurator {
        return $routes
            ->add($name, $path)
            ->defaults(['_locale' => '%locale%'])
            ->requirements(['_locale' => '^(?:%locales_regex%)$'])
            ->schemes('prod' === $environment ? ['https'] : ['http', 'https'])
        ;
    };

    $_import = static function (string $resource, string $type = 'annotation') use ($routes, $environment): ImportConfigurator {
        return $routes
            ->import($resource, $type)
            ->defaults(['_locale' => '%locale%'])
            ->requirements(['_locale' => '^(?:%locales_regex%)$'])
            ->schemes('prod' === $environment ? ['https'] : ['http', 'https'])
        ;
    };

    $routes->import($projectDir.'/src/Main/Controller/RootController.php', 'annotation')
        ->prefix('/', false)
    ;

    $_import($projectDir.'/src/Main/Controller/AssetsController.php')
        ->prefix('/{_locale}', false)
    ;

    $_route('user_login_check', '/{_locale}/login_check')
        ->methods(['POST'])
    ;

    $_route('user_logout', '/{_locale}/logout')
        ->methods(['GET', 'POST'])
    ;

    $_import($projectDir.'/src/User/Controller/')
        ->prefix('/{_locale}', false)
    ;

    $_import($projectDir.'/src/EsterenMaps/Controller/')
        ->prefix('/{_locale}', false)
    ;

    $_import($projectDir.'/src/EsterenMaps/Controller/Api')
        ->prefix('/{_locale}/api', false)
    ;

    $routes->import($projectDir.'/src/EsterenMaps/Controller/MapTileAssetController.php', 'annotation')
        ->prefix('/', false)
    ;

    $_import($projectDir.'/src/Admin/Controller/AdminController.php')
        ->prefix('/{_locale}/admin', false)
    ;

    return $routes;
};
