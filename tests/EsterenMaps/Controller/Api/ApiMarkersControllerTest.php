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

namespace Tests\EsterenMaps\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\GetHttpClientTestTrait;

class ApiMarkersControllerTest extends WebTestCase
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
            'latitude' => 20,
            'longitude' => 20,
            'altitude' => 0,
            'map' => 1,
            'markerType' => 1,
            'faction' => null,
        ];

        $client->request('POST', '/fr/api/markers', [], [], [], \json_encode($data));

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
    public function testCreateWithEmptyData(): void
    {
        $client = $this->getHttpClient('back.esteren.docker');
        $this->loginAsUser($client, 'standard-admin');

        $client->request('POST', '/fr/api/markers', [], [], [], '[]');

        static::assertSame(400, $client->getResponse()->getStatusCode());
        static::assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));

        $responseData = \json_decode($client->getResponse()->getContent(), true);

        static::assertArrayHasKey('title', $responseData);
        static::assertArrayHasKey('violations', $responseData);
        static::assertCount(3, $responseData['violations']);

        static::assertSame('name', $responseData['violations'][0]['propertyPath']);
        static::assertSame('Cette valeur ne doit pas être vide.', $responseData['violations'][0]['title']);

        static::assertSame('map', $responseData['violations'][1]['propertyPath']);
        static::assertSame('Cette valeur ne doit pas être vide.', $responseData['violations'][1]['title']);

        static::assertSame('markerType', $responseData['violations'][2]['propertyPath']);
        static::assertSame('Cette valeur ne doit pas être vide.', $responseData['violations'][2]['title']);
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
            'latitude' => 20,
            'longitude' => 20,
            'altitude' => 0,
            'map' => 9999999999,
            'markerType' => 9999999999,
            'faction' => 9999999999,
        ];

        $client->request('POST', '/fr/api/markers', [], [], [], \json_encode($dataToSend));

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = \json_decode($client->getResponse()->getContent(), true);

        static::assertArrayHasKey('title', $responseData);
        static::assertArrayHasKey('violations', $responseData);
        static::assertCount(2, $responseData['violations']);

        static::assertSame('map', $responseData['violations'][0]['propertyPath']);
        static::assertSame('Cette valeur ne doit pas être vide.', $responseData['violations'][0]['title']);

        static::assertSame('markerType', $responseData['violations'][1]['propertyPath']);
        static::assertSame('Cette valeur ne doit pas être vide.', $responseData['violations'][1]['title']);
    }
}
