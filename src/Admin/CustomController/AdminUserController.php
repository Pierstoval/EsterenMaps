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

namespace Admin\CustomController;

use Admin\Controller\AdminController;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use User\Entity\User;
use User\Mailer\UserMailer;
use User\Util\CanonicalizerTrait;
use User\Util\TokenGenerator;

class AdminUserController extends AdminController
{
    use CanonicalizerTrait;

    private UserPasswordHasherInterface $passwordEncoder;
    private UserMailer $mailer;

    public function __construct(
        UserPasswordHasherInterface $passwordEncoder,
        UserMailer $mailer
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
    }

    protected function initialize(Request $request): void
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', 'Only super admins can manage users.');

        parent::initialize($request);
    }

    protected function createEntityFormBuilder($entity, $view): FormBuilderInterface
    {
        $canonicalizer = \Closure::fromCallable([$this, 'canonicalize']);

        $builder = parent::createEntityFormBuilder($entity, $view);

        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($canonicalizer): void {
                /** @var User $user */
                $user = $event->getForm()->getData();
                $user->setUsernameCanonical($canonicalizer((string) $user->getUserIdentifier()));
                $user->setEmailCanonical($canonicalizer((string) $user->getEmail()));
            })
        ;

        return $builder;
    }

    /**
     * @param User $user
     */
    protected function persistEntity($user): void
    {
        if (!$hasPassword = $user->getPlainPassword()) {
            $user->setConfirmationToken(TokenGenerator::generateToken());
            $user->setPlainPassword(\uniqid('', true));
        }
        $user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPlainPassword()));
        $user->setEmailConfirmed(true);
        $user->eraseCredentials();

        // Causes the persist + flush
        parent::persistEntity($user);

        if (!$hasPassword) {
            // With no password, we send a "reset password" email to the user
            $this->mailer->sendResettingEmailMessage($user);
        }
    }
}
