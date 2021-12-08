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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\GetHttpClientTestTrait;

class DefaultEasyAdminTest extends WebTestCase
{
    use GetHttpClientTestTrait;

    /**
     * @group functional
     */
    public function test index returns 200 when logged in as admin(): void
    {
        $client = $this->getHttpClient();
        $this->loginAsUser($client, 'standard-admin');
        $crawler = $client->request('GET', '/fr/admin');

        static::assertSame(200, $client->getResponse()->getStatusCode(), \sprintf('Page title: "%s".', $crawler->filter('title')->html()));
        static::assertSame('Studio Agate', $crawler->filter('.content-wrapper h1')->text('', true));
    }

    /**
     * @dataProvider provide actions that need id
     * @group functional
     */
    public function test actions that need id must throw a 404 exception(string $action): void
    {
        $client = $this->getHttpClient();
        $this->loginAsUser($client, 'standard-admin');

        $crawler = $client->request('GET', "/fr/admin/Maps/{$action}");

        static::assertSame(404, $client->getResponse()->getStatusCode(), $crawler->filter('title')->html());
        static::assertSame('An id must be specified for this action.', $crawler->filter('h1.exception-message')->text('', true));
    }

    public function provide actions that need id(): \Generator
    {
        yield 'delete' => ['delete'];
        yield 'show' => ['show'];
        yield 'edit' => ['edit'];
    }
}
