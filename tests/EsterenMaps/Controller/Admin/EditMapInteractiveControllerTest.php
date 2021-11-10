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

namespace Tests\EsterenMaps\Controller\Admin;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\Panther\PantherTestCase;
use Tests\PantherLoginTrait;

/**
 * @group ux
 */
class EditMapInteractiveControllerTest extends PantherTestCase
{
    use PantherLoginTrait;

    /**
     * @runInSeparateProcess
     */
    public function testUpdateMarkerName(): void
    {
        $client = static::createPantherClient([
            'external_base_uri' => 'http://back.esteren.docker:8000',
        ]);

        $this->loginAs($client, 'pierstoval', 'admin');

        $client->request('GET', '/fr/maps/edit-interactive/1');

        static::assertSame(200, $client->getInternalResponse()->getStatusCode());

        $driver = $client->getWebDriver();

        $driver->findElement(WebDriverBy::cssSelector('#map'));

        // Osta-Baille
        $selector = WebDriverBy::cssSelector('[data-leaflet-marker-id="8"]');

        $driver->wait(2)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($selector)
        );

        $element = $driver->findElement($selector);

        static::assertNotNull($element);
        static::assertSame('markerType1', $element->getAttribute('data-leaflet-object-type'));
        static::assertSame('leaflet-marker-icon leaflet-zoom-animated leaflet-interactive', $element->getAttribute('class'));

        $element->click();

        $selector = WebDriverBy::cssSelector('#sidebar-tab-info.active form[name="api_markers"]');
        $driver->wait(3)->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));

        $element = $driver->findElement($selector);
        static::assertNotNull($element);

        $selector = WebDriverBy::id('api_markers_name');
        $driver->wait(3)->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));

        /** @var RemoteWebElement $markersNameInput */
        $markersNameInput = $driver->findElement($selector);
        static::assertNotNull($markersNameInput);
        static::assertSame('input', \strtolower($markersNameInput->getTagName()));
        $markersNameInput->clear();
        $markersNameInput->sendKeys('Osta-Baille test');

        $saveButton = $driver->findElement(WebDriverBy::cssSelector('[data-save][data-save-marker=""]'));
        $saveButton->click();

        $selector = WebDriverBy::cssSelector('#toast-container');
        $driver->wait(3)->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));
        $element = $driver->findElement($selector);
        static::assertNotNull($element);

        $selector = WebDriverBy::cssSelector('#toast-container');
        $driver->wait(3)->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));
        $element = $driver->findElement($selector);
        static::assertNotNull($element);
        $toastText = $element->getText();

        static::assertSame('Marker: 8 - Osta-Baille test', $toastText);
    }
}
