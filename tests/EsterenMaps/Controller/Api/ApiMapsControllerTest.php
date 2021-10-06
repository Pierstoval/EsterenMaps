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

namespace Tests\EsterenMaps\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Tests\GetHttpClientTestTrait;

class ApiMapsControllerTest extends WebTestCase
{
    use GetHttpClientTestTrait;

    /**
     * @group functional
     */
    public function test getting map api without role needs authentication(): void
    {
        $client = $this->getHttpClient('maps.esteren.docker');

        $client->request('GET', '/fr/api/maps/1');

        $response = $client->getResponse();
        static::assertSame(401, $response->getStatusCode());
    }

    /**
     * @group functional
     */
    public function test get map api data(): void
    {
        $client = $this->getHttpClient('maps.esteren.docker');
        $this->loginAsUser($client);

        $client->request('GET', '/fr/api/maps/1');

        static::assertResponseStatusCodeSame(200);

        $data = \json_decode($client->getResponse()->getContent(), true);

        if (\json_last_error()) {
            static::fail(\json_last_error_msg());
        }

        static::assertSame(1, $data['map']['id']);
        static::assertSame('tri-kazel', $data['map']['name_slug']);
        static::assertIsArray($data['map']['bounds']);
        $mapKeys = [
            'id', 'name', 'name_slug', 'image', 'description', 'max_zoom', 'start_zoom', 'start_x', 'start_y',
            'bounds', 'coordinates_ratio', 'markers', 'routes', 'zones',
        ];
        foreach ($mapKeys as $key) {
            static::assertArrayHasKey($key, $data['map']);
        }

        $element = new Crawler($data['templates']['LeafletPopupMarkerBaseContent']);
        static::assertCount(1, $element->filter('#marker_popup_name'));

        $element = new Crawler($data['templates']['LeafletPopupPolylineBaseContent']);
        static::assertCount(1, $element->filter('#polyline_popup_name'));

        $element = new Crawler($data['templates']['LeafletPopupPolygonBaseContent']);
        static::assertCount(1, $element->filter('#polygon_popup_name'));

        $element = new Crawler($data['templates']['LeafletPopupMarkerEditContent']);
        static::assertCount(1, $element->filter('form[name="api_markers"]'));
        static::assertCount(1, $element->filter('#api_markers'));

        $element = new Crawler($data['templates']['LeafletPopupPolylineEditContent']);
        static::assertCount(1, $element->filter('form[name="api_route"]'));
        static::assertCount(1, $element->filter('#api_route'));

        $element = new Crawler($data['templates']['LeafletPopupPolygonEditContent']);
        static::assertCount(1, $element->filter('form[name="api_zone"]'));
        static::assertCount(1, $element->filter('#api_zone'));
    }

    public function provideRolesToFetchMap()
    {
        yield 'ROLE_ADMIN' => ['ROLE_ADMIN'];
    }

    /**
     * @dataProvider provideRolesToFetchMap
     * @group functional
     */
    public function testMapInfo(string $role): void
    {
        $data = $this->getMapData($role);

        static::assertSame(1, $data['map']['id']);
        static::assertSame('tri-kazel', $data['map']['name_slug']);
        static::assertIsArray($data['map']['bounds']);
        $mapKeys = [
            'id', 'name', 'name_slug', 'image', 'description', 'max_zoom', 'start_zoom', 'start_x', 'start_y',
            'bounds', 'coordinates_ratio', 'markers', 'routes', 'zones',
        ];
        foreach ($mapKeys as $key) {
            static::assertArrayHasKey($key, $data['map']);
        }

        $element = new Crawler($data['templates']['LeafletPopupMarkerBaseContent']);
        static::assertCount(1, $element->filter('h3#marker_popup_name'));
        static::assertCount(1, $element->filter('p#marker_popup_type'));

        $element = new Crawler($data['templates']['LeafletPopupPolylineBaseContent']);
        static::assertCount(1, $element->filter('h3#polyline_popup_name'));
        static::assertCount(1, $element->filter('p#polyline_popup_type'));

        $element = new Crawler($data['templates']['LeafletPopupPolygonBaseContent']);
        static::assertCount(1, $element->filter('h3#polygon_popup_name'));
        static::assertCount(1, $element->filter('p#polygon_popup_type'));
    }

    /**
     * @dataProvider provideRolesToFetchMap
     * @group functional
     */
    public function testMapMarkers(string $role): void
    {
        $data = $this->getMapData($role);

        $marker = $data['map']['markers'][8];
        static::assertSame('Osta-Baille', $marker['name']);
        static::assertIsFloat($marker['latitude']);
        static::assertIsFloat($marker['longitude']);
        static::assertIsInt($marker['marker_type']);
        static::assertIsInt($marker['faction']);
        $markerKeys = ['id', 'name', 'description', 'latitude', 'longitude', 'marker_type', 'faction'];
        foreach ($markerKeys as $key) {
            static::assertArrayHasKey($key, $marker);
        }
    }

    /**
     * @dataProvider provideRolesToFetchMap
     * @group functional
     */
    public function testMapRoutes(string $role): void
    {
        $data = $this->getMapData($role);

        // Route
        $route = $data['map']['routes'][700];
        static::assertNotNull($route);
        static::assertSame('From 0,0 to 0,10', $route['name']);
        $routeKeys = [
            'id', 'name', 'description', 'coordinates', 'distance', 'guarded',
            'marker_start', 'marker_end', 'faction', 'route_type',
        ];
        foreach ($routeKeys as $key) {
            static::assertArrayHasKey($key, $route);
        }
        static::assertIsArray($route['coordinates']);
        static::assertArrayHasKey('lat', $route['coordinates'][0]);
        static::assertArrayHasKey('lng', $route['coordinates'][0]);
        static::assertIsNumeric($route['coordinates'][0]['lat']);
        static::assertIsNumeric($route['coordinates'][0]['lng']);
        static::assertIsNumeric($route['distance']);
        static::assertIsInt($route['route_type']);
        static::assertIsInt($route['marker_start']);
        static::assertIsInt($route['marker_end']);
        static::assertNull($route['faction']);
    }

    /**
     * @dataProvider provideRolesToFetchMap
     * @group functional
     */
    public function testMapZones(string $role): void
    {
        $data = $this->getMapData($role);

        $zone = $data['map']['zones'][1];
        static::assertNotNull($zone);
        /**
         * @dataProvider provideRolesToFetchMap
         */
        static::assertSame('Kingdom test', $zone['name']);
        foreach (['id', 'name', 'description', 'coordinates', 'faction', 'zone_type'] as $key) {
            static::assertArrayHasKey($key, $zone);
        }
        static::assertIsArray($zone['coordinates']);
        static::assertArrayHasKey('lat', $zone['coordinates'][0]);
        static::assertArrayHasKey('lng', $zone['coordinates'][0]);
        static::assertIsNumeric($zone['coordinates'][0]['lat']);
        static::assertIsNumeric($zone['coordinates'][0]['lng']);
        static::assertIsInt($zone['zone_type']);
        static::assertIsInt($zone['faction']);
    }

    /**
     * @dataProvider provideRolesToFetchMap
     * @group functional
     */
    public function testMapTemplates(string $role): void
    {
        $data = $this->getMapData($role);

        static::assertStringContainsString('id="marker_popup_name"', $data['templates']['LeafletPopupMarkerBaseContent'] ?? '');
        static::assertStringContainsString('id="polyline_popup_name"', $data['templates']['LeafletPopupPolylineBaseContent'] ?? '');
        static::assertStringContainsString('id="polygon_popup_name"', $data['templates']['LeafletPopupPolygonBaseContent'] ?? '');
    }

    /**
     * @dataProvider provideRolesToFetchMap
     * @group functional
     */
    public function testMapMarkersTypes(string $role): void
    {
        $data = $this->getMapData($role);

        $type = $data['references']['markers_types'][1];
        static::assertSame('City', $type['name']);
        $typeKeys = ['id', 'name', 'description', 'icon', 'icon_width', 'icon_height', 'icon_center_x', 'icon_center_y'];
        foreach ($typeKeys as $key) {
            static::assertArrayHasKey($key, $type);
        }
        static::assertIsInt($type['icon_width']);
        static::assertIsInt($type['icon_height']);
    }

    /**
     * @dataProvider provideRolesToFetchMap
     * @group functional
     */
    public function testMapRoutesTypes(string $role): void
    {
        $data = $this->getMapData($role);

        $type = $data['references']['routes_types'][1];
        static::assertSame('Track', $type['name']);
        foreach (['id', 'name', 'description', 'color'] as $key) {
            static::assertArrayHasKey($key, $type);
        }
        static::assertIsString($type['color']);
    }

    /**
     * @dataProvider provideRolesToFetchMap
     * @group functional
     */
    public function testMapZonesTypes(string $role): void
    {
        $data = $this->getMapData($role);

        $type = $data['references']['zones_types'][2];
        static::assertSame('Kingdom', $type['name']);
        foreach (['id', 'name', 'description', 'color', 'parent_id'] as $key) {
            static::assertArrayHasKey($key, $type);
        }
        static::assertIsString($type['color']);
        static::assertIsInt($type['parent_id']);
    }

    /**
     * @dataProvider provideRolesToFetchMap
     * @group functional
     */
    public function testMapFactions(string $role): void
    {
        $data = $this->getMapData($role);

        $type = $data['references']['factions'][1];
        static::assertSame('Faction Test', $type['name']);
        foreach (['id', 'name', 'description'] as $key) {
            static::assertArrayHasKey($key, $type);
        }
    }

    private function getMapData(string $role)
    {
        $client = $this->getHttpClient('maps.esteren.docker');

        static::$container
            ->get('doctrine.dbal.default_connection')
            ->prepare('UPDATE fos_user_user SET roles = :roles WHERE username = :username;')
            ->executeStatement([
                'roles' => \serialize(['ROLE_USER', $role]),
                'username' => 'lambda-user',
            ])
        ;
        $this->loginAsUser($client, 'lambda-user');

        $client->request('GET', '/fr/api/maps/1');

        $response = $client->getResponse();
        static::assertSame(200, $response->getStatusCode());
        $jsonContent = $response->getContent();
        $data = \json_decode($jsonContent, true, 512, \JSON_THROW_ON_ERROR);

        if (\json_last_error()) {
            static::fail(\json_last_error_msg());
        }

        return $data;
    }
}
