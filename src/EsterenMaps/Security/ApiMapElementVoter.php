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

namespace EsterenMaps\Security;

use EsterenMaps\Repository\MapElementRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use User\Entity\User;

class ApiMapElementVoter extends Voter
{
    public const MAX_NUMBER_OF_NOTES_PER_USER = 10;

    public const CAN_MANAGE_MAP_ELEMENTS = 'CAN_MANAGE_MAP_ELEMENTS';
    public const CAN_ADD_NOTES = 'CAN_ADD_NOTES';

    private const ATTRIBUTES = [
        self::CAN_MANAGE_MAP_ELEMENTS,
        self::CAN_ADD_NOTES,
    ];

    private RoleHierarchyInterface $roleHierarchy;
    private MapElementRepository $mapElementRepository;

    public function __construct(RoleHierarchyInterface $roleHierarchy, MapElementRepository $mapElementRepository)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->mapElementRepository = $mapElementRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, self::ATTRIBUTES, true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        switch ($attribute) {
            case self::CAN_MANAGE_MAP_ELEMENTS:
                return \in_array('ROLE_ADMIN', $this->roleHierarchy->getReachableRoleNames($user->getRoles() + $token->getRoleNames()), true);

            case self::CAN_ADD_NOTES:
                return $this->mapElementRepository->numberOfElementsForUser($user) <= self::MAX_NUMBER_OF_NOTES_PER_USER;

            default:
                throw new \LogicException('Unsupported attribute.');
        }
    }
}
