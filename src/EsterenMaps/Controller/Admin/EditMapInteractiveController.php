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

namespace EsterenMaps\Controller\Admin;

use EsterenMaps\Entity\Map;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EditMapInteractiveController extends AbstractController
{
    private $tileSize;

    public function __construct(int $tileSize)
    {
        $this->tileSize = (int) $tileSize;
    }

    /**
     * @Route("/admin/maps/edit-interactive/{id}", name="admin_esterenmaps_maps_maps_editInteractive", methods={"GET"})
     */
    public function __invoke(Request $request, Map $map): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (\count($request->query->all())) {
            // To avoid polluting the URL with useless query string.
            return $this->redirectToRoute($request->attributes->get('_route'), $request->attributes->get('_route_params'));
        }

        return $this->render('esteren_maps/AdminMaps/edit.html.twig', [
            'map' => $map,
            'tile_size' => $this->tileSize,
        ]);
    }
}
