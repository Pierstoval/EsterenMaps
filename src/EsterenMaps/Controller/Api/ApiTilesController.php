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
use EsterenMaps\Form\MapImageType;
use EsterenMaps\Model\MapImageQuery;
use EsterenMaps\Services\MapImageGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiTilesController extends AbstractController
{
    private string $outputDirectory;

    public function __construct(
        string $outputDirectory,
        private MapImageGenerator $mapImageGenerator,
        private TranslatorInterface $translator,
    ) {
        $this->outputDirectory = $outputDirectory;
    }

    /**
     * @Route("/maps/image/{id}", requirements={"id" = "\d+"}, name="esterenmaps_generate_map_image", methods={"GET"})
     */
    public function generateMapImageAction(Request $request, Map $map): Response
    {
        $baseData = new MapImageQuery();
        $form = $this->createForm(MapImageType::class, $baseData);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleFormSuccess($baseData, $map);
        }

        $messages = [];
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $messages[] = $this->translator->trans('field_error', ['%field%' => $field], 'validators').': '.$error->getMessage();
        }

        return new JsonResponse([
            'error' => true,
            'message' => $messages,
        ], 400);
    }

    /**
     * @Route(
     *     "/maps/tile/{id}/{zoom}/{x}/{y}.jpg",
     *     requirements={
     *         "id" = "\d+",
     *         "zoom" = "\d+",
     *         "x" = "\d+",
     *         "y" = "\d+"
     *     },
     *     name="esterenmaps_api_tiles",
     *     methods={"GET"}
     * )
     */
    public function tileAction(Map $map, int $zoom, int $x, int $y): Response
    {
        $file = $this->outputDirectory.$map->getId().'/'.$zoom.'/'.$x.'/'.$y.'.jpg';

        if (!\file_exists($file)) {
            $file = $this->outputDirectory.'/empty.jpg';
        }

        return (new BinaryFileResponse($file, 200, ['Content-Type' => 'image/jpeg']))
            ->setMaxAge(864000)
        ;
    }

    private function handleFormSuccess(MapImageQuery $data, Map $map): Response
    {
        try {
            // TODO: refactor this and use a better output system, like using JPG instead of PSD.

            $imageName = $this->outputDirectory.'/output_'.$map->getNameSlug().'.psd';

            if (!\file_exists($imageName)) {
                $this->mapImageGenerator->generateImage($map, $imageName);
            }

            return (new BinaryFileResponse(
                $imageName,
                200,
                ['Content-Type' => 'image/jpeg']
            ))
                ->setPublic()
                ->setExpires(new \DateTime('+1 day'))
            ;
        } catch (\Exception $e) {
            $message = '';
            do {
                $message .= ($message ? "\n" : '').$e->getMessage();
            } while ($e = $e->getPrevious());

            return new JsonResponse([
                'error' => true,
                'message' => $message,
            ], 400);
        }
    }
}
