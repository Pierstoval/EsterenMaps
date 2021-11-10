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

use Agate\Entity\PortalElement;
use Doctrine\ORM\EntityManagerInterface;
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
    public function test root redirects with locale(string $browserLocale, string $host, string $expectedLocale = null): void
    {
        $client = $this->getHttpClient($host);
        $this->loginAsUser($client, 'standard-admin');

        if (null === $expectedLocale) {
            $expectedLocale = $browserLocale;
        }

        $client->request('GET', '/', [], [], [
            'HTTP_ACCEPT_LANGUAGE' => [$browserLocale],
        ]);

        static::assertSame(301, $client->getResponse()->getStatusCode());
        static::assertTrue($client->getResponse()->isRedirect("/{$expectedLocale}"));
    }

    public function provideRootData(): ?\Generator
    {
        yield 0 => ['fr', 'portal.esteren.docker'];
        yield 1 => ['fr', 'maps.esteren.docker'];
        yield 3 => ['fr', 'games.esteren.docker'];
        yield 4 => ['fr', 'api.esteren.docker'];
        yield 5 => ['fr', 'back.esteren.docker'];
        yield 6 => ['fr', 'www.studio-agate.docker'];
        yield 7 => ['fr', 'stats.studio-agate.docker'];
        yield 8 => ['fr', 'www.dragons-rpg.docker'];
        yield 9 => ['fr', 'vermine2047.docker'];
        yield 9 => ['fr', 'totem-system.docker'];
        yield 10 => ['en', 'portal.esteren.docker'];
        yield 11 => ['en', 'maps.esteren.docker'];
        yield 13 => ['en', 'games.esteren.docker'];
        yield 14 => ['en', 'api.esteren.docker'];
        yield 15 => ['en', 'back.esteren.docker'];
        yield 16 => ['en', 'www.studio-agate.docker'];
        yield 17 => ['en', 'stats.studio-agate.docker'];
        yield 18 => ['en', 'www.dragons-rpg.docker'];
        yield 19 => ['en', 'vermine2047.docker'];
        yield 20 => ['en', 'totem-system.docker'];

        // Fateforge specific case: must always redirect to "en"
        yield 21 => ['fr', 'fateforge.docker', 'en'];
        yield 22 => ['en', 'fateforge.docker'];
    }

    /**
     * @dataProvider provideTestUrls
     */
    public function test urls(
        string $domainName,
        string $url,
        ?string $expectedRouteName,
        int $expectedStatusCode = 200,
        string $expectedRedirectUrlOrTitleContent = '',
        string $cssSelectorToCheck = '#content h1',
        string $userLocale = 'fr',
        ?string $loggedInUsername = null
    ): void {
        $client = $this->getHttpClient($domainName);

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
        yield 'www.studio-agate.docker/fr' => ['www.studio-agate.docker', '/fr', 'agate_portal_home', 200, 'Bienvenue sur le nouveau portail du Studio Agate'];
        yield 'www.studio-agate.docker/en' => ['www.studio-agate.docker', '/en', 'agate_portal_home', 200, 'Welcome to the new Studio Agate portal'];
        yield 'www.studio-agate.docker/fr/team' => ['www.studio-agate.docker', '/fr/team', 'agate_team', 200, 'L\'Équipe du studio Agate'];
        yield 'www.studio-agate.docker/en/team' => ['www.studio-agate.docker', '/en/team', 'agate_team', 200, 'The Studio Agate team'];
        yield 'www.studio-agate.docker/fr/legal' => ['www.studio-agate.docker', '/fr/legal', 'legal_mentions', 200, 'Mentions légales', '#content h2'];
        yield 'www.studio-agate.docker/en/legal' => ['www.studio-agate.docker', '/en/legal', 'legal_mentions', 404];

        // Vermine portal
        yield 'vermine2047.docker/fr' => ['vermine2047.docker', '/fr', 'vermine_portal_home', 200, 'Portail Vermine', 'h2'];
        yield 'vermine2047.docker/en' => ['vermine2047.docker', '/en', 'vermine_portal_home', 200, 'Vermine Portal', 'h2'];

        // Totem portal
        yield 'totem-system.docker/fr' => ['totem-system.docker', '/fr', 'totem_portal_home', 200, 'Système de jeu Totem', 'title'];
        yield 'totem-system.docker/en' => ['totem-system.docker', '/en', 'totem_portal_home', 200, 'Totem System for role-playing games', 'title'];

        // Esteren portal
        yield 'portal.esteren.docker/fr' => ['portal.esteren.docker', '/fr', 'esteren_portal_home', 200, 'Bienvenue sur le nouveau portail des Ombres d\'Esteren'];
        yield 'portal.esteren.docker/en' => ['portal.esteren.docker', '/en', 'esteren_portal_home', 200, 'Welcome to the new Shadows of Esteren\'s portal!'];
        yield 'portal.esteren.docker/fr/feond-beer' => ['portal.esteren.docker', '/fr/feond-beer', 'esteren_portal_feond_beer', 200, 'La bière du Féond', 'title'];
        yield 'portal.esteren.docker/en/feond-beer' => ['portal.esteren.docker', '/en/feond-beer', 'esteren_portal_feond_beer', 200, 'La bière du Féond', 'title'];

        // Dragons
        yield 'www.dragons-rpg.docker/fr' => ['www.dragons-rpg.docker', '/fr', 'dragons_home', 200, 'Bienvenue sur le nouveau portail du jeu de rôle Dragons'];
        yield 'www.dragons-rpg.docker/en' => ['www.dragons-rpg.docker', '/en', 'dragons_home', 302, '//fateforge.docker/en'];
        yield 'fateforge.docker/en' => ['fateforge.docker', '/en', 'fateforge_home', 200, 'Fateforge RPG', 'title'];
        yield 'fateforge.docker/fr' => ['fateforge.docker', '/fr', 'fateforge_home', 302, '//www.dragons-rpg.docker/'];

        // Assets
        yield 'www.studio-agate.docker/fr/js/translations' => ['www.studio-agate.docker', '/fr/js/translations', 'pierstoval_tools_assets_jstranslations', 200];
        yield 'vermine2047.docker/fr/js/translations' => ['vermine2047.docker', '/fr/js/translations', 'pierstoval_tools_assets_jstranslations', 200];
        yield 'portal.esteren.docker/fr/js/translations' => ['portal.esteren.docker', '/fr/js/translations', 'pierstoval_tools_assets_jstranslations', 200];
        yield 'maps.esteren.docker/fr/js/translations' => ['maps.esteren.docker', '/fr/js/translations', 'pierstoval_tools_assets_jstranslations', 200];
        yield 'www.dragons-rpg.docker/fr/js/translations' => ['www.dragons-rpg.docker', '/fr/js/translations', 'pierstoval_tools_assets_jstranslations', 200];
    }

    /**
     * @dataProvider providePortalsThatShouldReturn404IfNotDefined
     */
    public function test portal not defined returns 404(string $host, string $url): void
    {
        static::bootKernel();

        $linesRemoved = static::$container
            ->get(EntityManagerInterface::class)
            ->createQuery('DELETE FROM '.PortalElement::class)
            ->execute()
        ;

        static::ensureKernelShutdown();

        static::assertGreaterThan(0, $linesRemoved);

        $client = $this->getHttpClient($host);

        $client->request('GET', $url);

        static::assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function providePortalsThatShouldReturn404IfNotDefined()
    {
        // Studio Agate
        yield 'www.studio-agate.docker/fr' => ['www.studio-agate.docker', '/fr'];
        yield 'www.studio-agate.docker/en' => ['www.studio-agate.docker', '/en'];

        // Esteren portal
        yield 'portal.esteren.docker/fr' => ['portal.esteren.docker', '/fr'];
        yield 'portal.esteren.docker/en' => ['portal.esteren.docker', '/en'];

        // Dragons
        yield 'www.dragons-rpg.docker/fr' => ['www.dragons-rpg.docker', '/fr'];
    }
}
