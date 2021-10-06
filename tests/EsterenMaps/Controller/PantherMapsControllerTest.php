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

namespace Tests\EsterenMaps\Controller;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\Panther\PantherTestCase;
use Tests\PantherLoginTrait;

class PantherMapsControllerTest extends PantherTestCase
{
    use PantherLoginTrait;

    /**
     * @group ux
     * @runInSeparateProcess
     */
    public function testMap(): void
    {
        $client = static::createPantherClient(['external_base_uri' => 'http://maps.esteren.docker:8000']);

        $this->loginAs($client, 'pierstoval', 'admin');

        $client->request('GET', '/fr/map-tri-kazel');

        static::assertSame(200, $client->getInternalResponse()->getStatusCode());

        $driver = $client->getWebDriver();

        // Wait for map to load
        $selector = WebDriverBy::id('map');
        $driver->wait(5, 500)->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));
        $driver->findElement($selector);

        // Find Tuaille marker
        $selector = WebDriverBy::cssSelector('[data-leaflet-marker-id="7"]');
        $driver->wait(5, 1000)->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));
        $element = $driver->findElement($selector);
        static::assertNotNull($element);
        static::assertSame('markerType2', $element->getAttribute('data-leaflet-object-type'));
        static::assertSame('leaflet-marker-icon leaflet-zoom-animated leaflet-interactive', $element->getAttribute('class'));

        // Clicking should display popup information
        $element->click();

        $selector = WebDriverBy::id('marker_popup_type');
        $driver->wait(3)->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));
        $element = $driver->findElement($selector);
        static::assertNotNull($element);
        static::assertSame('Shipyard', $element->getText());
    }
}
