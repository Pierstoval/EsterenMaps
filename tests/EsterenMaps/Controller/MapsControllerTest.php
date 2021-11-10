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

namespace Tests\EsterenMaps\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Link;
use Tests\GetHttpClientTestTrait;

class MapsControllerTest extends WebTestCase
{
    use GetHttpClientTestTrait;

    /**
     * @group functional
     */
    public function test index(): void
    {
        $client = $this->getHttpClient('maps.esteren.docker');

        $crawler = $client->request('GET', '/fr');

        static::assertSame(200, $client->getResponse()->getStatusCode());

        $article = $crawler->filter('.maps-list article');
        static::assertGreaterThanOrEqual(1, $article->count(), $crawler->filter('title')->text('', true));

        $link = $article->filter('a')->link();

        static::assertInstanceOf(Link::class, $link);
        static::assertSame('Voir la carte', \trim($link->getNode()->textContent));
        static::assertSame('http://maps.esteren.docker/fr/map-tri-kazel', \trim($link->getUri()));
    }

    /**
     * @group functional
     */
    public function test view while not logged in should trigger authentication(): void
    {
        $client = $this->getHttpClient('maps.esteren.docker');

        $client->request('GET', '/fr/map-tri-kazel');
        $res = $client->getResponse();

        static::assertSame(401, $res->getStatusCode());
    }

    /**
     * @group functional
     */
    public function test view when authenticated accessible(): void
    {
        $client = $this->getHttpClient('maps.esteren.docker');

        $this->loginAsUser($client);

        $crawler = $client->request('GET', '/fr/map-tri-kazel');
        $res = $client->getResponse();

        static::assertSame(200, $res->getStatusCode());
        static::assertSame('Tri-Kazel', $crawler->filter('h1')->text('', true));
    }

    /**
     * @group functional
     */
    public function test view while connected is accessible for admin(): void
    {
        $client = $this->getHttpClient('maps.esteren.docker');

        $this->loginAsUser($client, 'standard-admin');

        $crawler = $client->request('GET', '/fr/map-tri-kazel');
        $res = $client->getResponse();

        static::assertSame(200, $res->getStatusCode());
        static::assertCount(1, $crawler->filter('#map_wrapper'), 'Map link does not redirect to map view, or map view is broken');
    }

    /**
     * @group functional
     */
    public function test view while connected is accessible for user with active subscription(): void
    {
        $client = $this->getHttpClient('maps.esteren.docker');

        $this->loginAsUser($client, 'map-subscribed');

        $crawler = $client->request('GET', '/fr/map-tri-kazel');
        $res = $client->getResponse();

        static::assertSame(200, $res->getStatusCode());
        static::assertCount(1, $crawler->filter('#map_wrapper'), "Map link does not redirect to map view, or map view is broken.\n".$crawler->filter('title')->text('No title', true));
    }
}
