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

namespace EsterenMaps\Controller;

use EsterenMaps\Entity\Map;
use EsterenMaps\Repository\MapsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MapsController extends AbstractController
{
    private MapsRepository $mapsRepository;

    public function __construct(MapsRepository $mapsRepository)
    {
        $this->mapsRepository = $mapsRepository;
    }

    /**
     * @Route("/", methods={"GET"}, name="esterenmaps_maps_list")
     */
    public function indexAction(): Response
    {
        /** @var Map[] $allMaps */
        $allMaps = $this->mapsRepository->findAll();

        return $this->render('esteren_maps/Maps/index.html.twig', [
            'list' => $allMaps,
        ]);
    }

    /**
     * @Route("/map-{nameSlug}", methods={"GET"}, name="esterenmaps_maps_maps_view")
     */
    public function viewAction(string $nameSlug): Response
    {
        /** @var Map $map */
        $map = $this->mapsRepository->findOneBy(['nameSlug' => $nameSlug]);

        $response = new Response();
        $response->setCache([
            'max_age' => 600,
            's_maxage' => 3600,
            'public' => false,
        ]);

        if (!$this->getUser()) {
            throw new AccessDeniedException('You must be logged in to view maps.');
        }

        return $this->render('esteren_maps/Maps/view.html.twig', [
            'map' => $map,
            'tile_size' => $this->getParameter('esterenmaps.tile_size'),
        ], $response);
    }
}
