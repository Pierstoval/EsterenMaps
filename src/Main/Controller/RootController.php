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

namespace Main\Controller;

use Main\DependencyInjection\PublicService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RootController implements PublicService
{
    private $locales;

    public function __construct(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * Don't rename the "root" route, it must stay the same,
     * because of the yaml "hack" it induces.
     * Check "routes.yaml" for more information.
     *
     * @Route("", methods={"GET"}, name="root")
     */
    public function __invoke(Request $request, string $_locale = null): Response
    {
        if (!$_locale) {
            $_locale = $request->getPreferredLanguage(\array_values($this->locales));
        }

        return new RedirectResponse("/{$_locale}", 301);
    }
}
