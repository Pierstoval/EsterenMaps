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
use Tests\GetHttpClientTestTrait;

class ApiZonesControllerTest extends WebTestCase
{
    use GetHttpClientTestTrait;

    /**
     * @group functional
     */
    public function testCreateWithCorrectData(): void
    {
        $client = $this->getHttpClient('back.esteren.docker');
        $this->loginAsUser($client, 'standard-admin');

        $data = [
            'name' => 'Test name',
            'description' => 'Test description',
            'coordinates' => '[{"lat":"0","lng":"10"}]',
            'map' => 1,
            'zoneType' => 1,
            'faction' => null,
        ];

        $client->request('POST', '/fr/api/zones', [], [], [], \json_encode($data));

        static::assertSame(200, $client->getResponse()->getStatusCode());
        static::assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));

        // Add ID For assertion
        $data['id'] = 704;
        $responseData = \json_decode($client->getResponse()->getContent(), true);
        static::assertSame(\ksort($data), \ksort($responseData));
    }

    /**
     * @group functional
     */
    public function testCreationFlattensLatLngCorrectly(): void
    {
        $client = $this->getHttpClient('back.esteren.docker');
        $this->loginAsUser($client, 'standard-admin');

        $data = [
            'name' => 'Test zone to flatten coordinates',
            'description' => 'Description',
            'coordinates' => '[[{"lat":"0","lng":"10"}],[{"lat":"10","lng":"10"}]]',
            'map' => 1,
            'zoneType' => 1,
            'faction' => null,
        ];

        $client->request('POST', '/fr/api/zones', [], [], [], \json_encode($data));

        static::assertSame(200, $client->getResponse()->getStatusCode());
        static::assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));

        // Add ID For assertion
        $data['id'] = 704;
        $responseData = \json_decode($client->getResponse()->getContent(), true);
        static::assertSame('[[{"lat":"0","lng":"10"}],[{"lat":"10","lng":"10"}]]', $responseData['coordinates']);
    }

    /**
     * @group functional
     */
    public function testCreateWithEmptyData(): void
    {
        $client = $this->getHttpClient('back.esteren.docker');
        $this->loginAsUser($client, 'standard-admin');

        $client->request('POST', '/fr/api/zones', [], [], [], '[]');

        static::assertSame(400, $client->getResponse()->getStatusCode());
        static::assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));

        $expectedResponse = [
            'name' => 'Cette valeur ne doit pas être vide.',
            'map' => 'Cette valeur ne doit pas être vide.',
            'zoneType' => 'Cette valeur ne doit pas être vide.',
        ];

        $responseData = \json_decode($client->getResponse()->getContent(), true);

        static::assertArrayHasKey('title', $responseData);
        static::assertArrayHasKey('violations', $responseData);
        static::assertCount(3, $responseData['violations']);

        $i = 0;

        static::assertSame('name', $responseData['violations'][$i]['propertyPath']);
        static::assertSame('Cette valeur ne doit pas être vide.', $responseData['violations'][$i++]['title']);

        static::assertSame('map', $responseData['violations'][$i]['propertyPath']);
        static::assertSame('Cette valeur ne doit pas être vide.', $responseData['violations'][$i++]['title']);

        static::assertSame('zoneType', $responseData['violations'][$i]['propertyPath']);
        static::assertSame('Cette valeur ne doit pas être vide.', $responseData['violations'][$i++]['title']);
    }

    /**
     * @group functional
     */
    public function testCreateWithIncorrectData(): void
    {
        $client = $this->getHttpClient('back.esteren.docker');
        $this->loginAsUser($client, 'standard-admin');

        $dataToSend = [
            'name' => 'Test name',
            'description' => 'Test description',
            'coordinates' => '[{"lat":"0","lng":"10"}]',
            'map' => 9999999999,
            'zoneType' => 9999999999,
            'faction' => 9999999999,
        ];

        $client->request('POST', '/fr/api/zones', [], [], [], \json_encode($dataToSend));

        static::assertSame(400, $client->getResponse()->getStatusCode());
        static::assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));

        $responseData = \json_decode($client->getResponse()->getContent(), true);

        static::assertArrayHasKey('title', $responseData);
        static::assertArrayHasKey('violations', $responseData);
        static::assertCount(2, $responseData['violations']);

        $i = 0;

        static::assertSame('map', $responseData['violations'][$i]['propertyPath']);
        static::assertSame('Cette valeur ne doit pas être vide.', $responseData['violations'][$i++]['title']);

        static::assertSame('zoneType', $responseData['violations'][$i]['propertyPath']);
        static::assertSame('Cette valeur ne doit pas être vide.', $responseData['violations'][$i++]['title']);
    }
}
