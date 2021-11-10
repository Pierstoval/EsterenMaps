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

use Doctrine\ORM\EntityManagerInterface;
use EsterenMaps\Api\MapElementPayloadSanitizerInterface;
use EsterenMaps\DTO\Api\MapElementDTOInterface;
use EsterenMaps\Entity\MapElementInterface;
use EsterenMaps\Security\ApiMapElementVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ApiMapElementTrait
{
    protected Security $security;
    protected EntityManagerInterface $em;
    protected SerializerInterface $serializer;
    protected ValidatorInterface $validator;
    protected MapElementPayloadSanitizerInterface $mapElementSanitizer;

    public function __construct(
        Security $security,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->security = $security;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    protected function updateAction(Request $request, string $dtoClass, MapElementInterface $mapElement): JsonResponse
    {
        if (!\is_a($dtoClass, MapElementDTOInterface::class, true)) {
            throw new \InvalidArgumentException(\sprintf(
                'DTO class must implement %s, %s given.',
                MapElementDTOInterface::class,
                $dtoClass
            ));
        }

        if (!$this->hasAccess()) {
            throw new AccessDeniedException();
        }

        if ($mapElement->isNoteFrom() && !$mapElement->isNoteFromUser($this->security->getUser())) {
            throw new AccessDeniedException('You cannot edit this object.');
        }

        $payload = \json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $payload = $this->mapElementSanitizer->sanitizeRequestData($payload);

        $dto = $dtoClass::fromMapElementAndApiPayload($mapElement, $payload);

        $violations = $this->validator->validate($dto);

        if (\count($violations)) {
            return new JsonResponse($this->serializer->serialize($violations, 'json'), 400, [], true);
        }

        $mapElement->updateFromApi($dto);

        $this->em->persist($mapElement);
        $this->em->flush();

        return new JsonResponse($mapElement, 200);
    }

    protected function createAction(Request $request, string $dtoClass, string $entityClass): JsonResponse
    {
        if (!\is_a($dtoClass, MapElementDTOInterface::class, true)) {
            throw new \InvalidArgumentException(\sprintf(
                'DTO class must implement %s, %s given.',
                MapElementDTOInterface::class,
                $dtoClass
            ));
        }

        if (!\is_a($entityClass, MapElementInterface::class, true)) {
            throw new \InvalidArgumentException(\sprintf(
                'Entity class must implement %s, %s given.',
                MapElementInterface::class,
                $entityClass
            ));
        }

        if (!$this->hasAccess()) {
            throw new AccessDeniedException();
        }

        $payload = \json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $payload = $this->mapElementSanitizer->sanitizeRequestData($payload);

        $dto = $dtoClass::fromApiPayload($payload);

        $violations = $this->validator->validate($dto);

        if (\count($violations)) {
            return new JsonResponse($this->serializer->serialize($violations, 'json'), 400, [], true);
        }

        $mapElement = $entityClass::fromApi($dto);

        $this->em->persist($mapElement);
        $this->em->flush();

        return new JsonResponse($mapElement, 200);
    }

    protected function deleteAction(MapElementInterface $mapElement): JsonResponse
    {
        if (!$this->hasAccess()) {
            throw new AccessDeniedException();
        }

        if ($mapElement->isNoteFrom() && !$mapElement->isNoteFromUser($this->security->getUser())) {
            throw new AccessDeniedException('You cannot edit this object.');
        }

        $this->em->remove($mapElement);
        $this->em->flush();

        return new JsonResponse(null, 200);
    }

    private function hasAccess(): bool
    {
        return
            $this->security->isGranted(ApiMapElementVoter::CAN_MANAGE_MAP_ELEMENTS)
            || (
                $this->security->isGranted('SUBSCRIBED_TO_MAPS')
                && $this->security->isGranted(ApiMapElementVoter::CAN_ADD_NOTES)
            )
        ;
    }
}
