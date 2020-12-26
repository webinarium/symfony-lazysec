<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2018-2020 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace LazySec\Checker;

use LazySec\Entity\DisableAccountTrait;
use LazySec\Entity\ExpireAccountTrait;
use LazySec\Entity\ExpirePasswordTrait;
use LazySec\Entity\LockAccountTrait;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Generic user checker which autodetects included LazySec traits.
 */
class GenericUserChecker implements UserCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (in_array(LockAccountTrait::class, class_uses($user), true)) {
            /** @var LockAccountTrait|UserInterface $user */
            if (!$user->isAccountNonLocked()) {
                $exception = new LockedException('User account is locked.');
                $exception->setUser($user);
                throw $exception;
            }
        }

        if (in_array(DisableAccountTrait::class, class_uses($user), true)) {
            /** @var DisableAccountTrait|UserInterface $user */
            if (!$user->isEnabled()) {
                $exception = new DisabledException('User account is disabled.');
                $exception->setUser($user);
                throw $exception;
            }
        }

        if (in_array(ExpireAccountTrait::class, class_uses($user), true)) {
            /** @var ExpireAccountTrait|UserInterface $user */
            if (!$user->isAccountNonExpired()) {
                $exception = new AccountExpiredException('User account has expired.');
                $exception->setUser($user);
                throw $exception;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        if (in_array(ExpirePasswordTrait::class, class_uses($user), true)) {
            /** @var ExpirePasswordTrait|UserInterface $user */
            if (!$user->isCredentialsNonExpired()) {
                $exception = new CredentialsExpiredException('User credentials have expired.');
                $exception->setUser($user);
                throw $exception;
            }
        }
    }
}
