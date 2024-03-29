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

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group functional
 */
class StaticUrlsTest extends WebTestCase
{
    use GetHttpClientTestTrait;

    /**
     * @dataProvider provideRootData
     */
    public function test root redirects with locale(string $browserLocale): void
    {
        $client = $this->getHttpClient();
        $this->loginAsUser($client, 'standard-admin');

        $client->request('GET', '/', [], [], [
            'HTTP_ACCEPT_LANGUAGE' => [$browserLocale],
        ]);

        static::assertSame(301, $client->getResponse()->getStatusCode());
        static::assertTrue($client->getResponse()->isRedirect("/{$browserLocale}"));
    }

    public function provideRootData(): ?\Generator
    {
        yield 0 => ['fr'];
        yield 1 => ['en'];
    }

    /**
     * @dataProvider provideTestUrls
     */
    public function test urls(
        string $url,
        ?string $expectedRouteName,
        int $expectedStatusCode = 200,
        string $expectedRedirectUrlOrTitleContent = '',
        string $cssSelectorToCheck = '#content h1',
        string $userLocale = 'fr',
        ?string $loggedInUsername = null
    ): void {
        $client = $this->getHttpClient();

        if ($loggedInUsername) {
            $this->loginAsUser($client, $loggedInUsername);
        }

        $crawler = $client->request('GET', $url, [], [], [
            'HTTP_ACCEPT_LANGUAGE' => [$userLocale],
        ]);

        $req = $client->getRequest();
        $res = $client->getResponse();

        static::assertSame($expectedRouteName, $req->attributes->get('_route'), 'Unexpected route name.');
        static::assertSame($expectedStatusCode, $res->getStatusCode(), 'Unexpected status code.');

        if ($expectedRedirectUrlOrTitleContent) {
            // See Symfony\Component\HttpFoundation\Response::isRedirect()
            if (\in_array($expectedStatusCode, [201, 301, 302, 303, 307, 308], true)) {
                $message = \sprintf(
                    'Unexpected redirect url. Expected "%s", got "%s".',
                    $expectedRedirectUrlOrTitleContent,
                    $res->headers->get('Location')
                );
                static::assertTrue($res->isRedirect($expectedRedirectUrlOrTitleContent), $message);
            } else {
                $title = $crawler->filter($cssSelectorToCheck);
                static::assertNotNull($title, 'No node found for the CSS selector.');
                static::assertSame($expectedRedirectUrlOrTitleContent, $title->text('', true));
            }
        }
    }

    public function provideTestUrls(): ?\Generator
    {
        // Studio Agate
        yield '/fr' => ['/fr', 'esterenmaps_maps_list', 200, 'Liste des cartes'];
        yield '/en' => ['/en', 'esterenmaps_maps_list', 200, 'Maps list'];

        // Assets
        yield '/fr/translations' => ['/fr/js/translations', 'pierstoval_tools_assets_jstranslations', 200];
        yield '/en/translations' => ['/en/js/translations', 'pierstoval_tools_assets_jstranslations', 200];
    }
}
