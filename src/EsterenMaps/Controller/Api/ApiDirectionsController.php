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

namespace EsterenMaps\Controller\Api;

use EsterenMaps\Entity\Map;
use EsterenMaps\Entity\Marker;
use EsterenMaps\Repository\TransportTypesRepository;
use EsterenMaps\Services\DirectionsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiDirectionsController extends AbstractController
{
    public function __construct(
        private TransportTypesRepository $transportTypesRepository,
        private DirectionsManager $directionsManager,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @Route("/maps/directions/{id}/{from}/{to}",
     *     name="esterenmaps_directions",
     *     requirements={"id" = "\d+", "from" = "\d+", "to" = "\d+"},
     *     methods={"GET"}
     * )
     * @ParamConverter(name="from", class="EsterenMaps\Entity\Marker", options={"id" = "from"})
     * @ParamConverter(name="to", class="EsterenMaps\Entity\Marker", options={"id" = "to"})
     */
    public function __invoke(Map $map, Marker $from, Marker $to, Request $request): JsonResponse
    {
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $transportId = $request->query->get('transport');
        $transport = $this->transportTypesRepository->findOneBy(['id' => $transportId]);

        $response = new JsonResponse();
        $response->setCache([
            'max_age' => 600,
            's_maxage' => 3600,
            'public' => true,
        ]);

        if (!$transport && $transportId) {
            $output = $this->getError($from, $to, $transportId, 'Transport not found.');
            $response->setStatusCode(404);
        } else {
            $output = $this->directionsManager->getDirections($map, $from, $to, (int) $request->query->get('hours_per_day', 7), $transport);
            if (0 === \count($output)) {
                $output = $this->getError($from, $to);
                $response->setStatusCode(404);
            }
        }

        $response->setData($output);

        return $response;
    }

    private function getError(Marker $from, Marker $to, int $transportId = null, string $message = 'No path found for this query.'): array
    {
        return [
            'error' => true,
            'message' => $this->translator->trans($message),
            'query' => [
                'from' => $from,
                'to' => $to,
                'transport' => $transportId,
            ],
        ];
    }
}
