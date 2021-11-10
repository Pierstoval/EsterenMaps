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

namespace Tests\Admin;

use EsterenMaps\Entity\Map;

class MapsAdminTest extends AbstractEasyAdminTest
{
    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return 'Maps';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return Map::class;
    }

    /**
     * {@inheritdoc}
     */
    public function provideListingFields()
    {
        return [
            'id',
            'name',
            'nameSlug',
            'maxZoom',
            'startZoom',
            'startX',
            'startY',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideNewFormData()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function provideEditFormData()
    {
        return false;
    }

    /**
     * @group functional
     */
    public function test edit interactive route returns 200(): void
    {
        $client = $this->getHttpClient('back.esteren.docker');
        $this->loginAsUser($client, 'standard-admin');

        $crawler = $client->request('GET', '/fr/maps/edit-interactive/1');

        static::assertSame(200, $client->getResponse()->getStatusCode());
        static::assertCount(1, $crawler->filter('div#esterenmap_sidebar'));
        static::assertCount(1, $crawler->filter('div#map'));
    }
}
