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

namespace User\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use User\Entity\User;
use User\Util\CanonicalizerTrait;

/**
 * @method null|User findOneBy(array $criteria, array $orderBy = null)
 * @method null|User find($id, $lockMode = null, $lockVersion = null)
 */
class UserRepository extends ServiceEntityRepository implements UserProviderInterface
{
    use CanonicalizerTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByIdentifier(string $identifier): User
    {
        $user = $this->findByUsernameOrEmail($identifier);

        if (!$user) {
            $exception = new UserNotFoundException();
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }

    public function findByUsernameOrEmail(string $usernameOrEmail): ?User
    {
        return $this->createQueryBuilder('user')
            ->where('user.usernameCanonical = :usernameOrEmail')
            ->orWhere('user.emailCanonical = :usernameOrEmail')
            ->setParameter('usernameOrEmail', $this->canonicalize($usernameOrEmail))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByEmail($email): ?User
    {
        return $this->findOneBy(['emailCanonical' => $this->canonicalize($email)]);
    }

    public function findOneByConfirmationToken($token): ?User
    {
        return $this->findOneBy(['confirmationToken' => $token]);
    }

    public function loadUserByUsername($username): User
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user): User
    {
        if (!($user instanceof User)) {
            throw new UnsupportedUserException(\sprintf('Expected an instance of %s, but got "%s".', User::class, \get_class($user)));
        }

        if (null === $reloadedUser = $this->find($user->getId())) {
            throw new UserNotFoundException(\sprintf('User with ID "%s" could not be reloaded.', $user->getId()));
        }

        return $reloadedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return User::class === $class;
    }
}
