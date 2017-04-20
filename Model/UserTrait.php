<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * User trait.
 *
 * @ORM\MappedSuperclass(repositoryClass="Pignus\Model\UserRepositoryInterface")
 */
trait UserTrait
{
    /**
     * @see \Symfony\Component\Security\Core\User\UserInterface
     *
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @see \Symfony\Component\Security\Core\User\UserInterface
     *
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @see \Symfony\Component\Security\Core\User\UserInterface
     *
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        if (in_array(DisableAccountTrait::class, class_uses($this), true)) {
            /** @var DisableAccountTrait $this */
            return $this->canAccountBeDisabled() ? $this->isEnabled : true;
        }

        return true;
    }

    /**
     * @see \Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        if (in_array(ExpireAccountTrait::class, class_uses($this), true)) {
            /** @var ExpireAccountTrait $this */
            return $this->canAccountBeExpired()
                ? $this->accountExpiresAt === null || $this->accountExpiresAt > time()
                : true;
        }

        return true;
    }

    /**
     * @see \Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        if (in_array(ExpirePasswordTrait::class, class_uses($this), true)) {
            /** @var ExpirePasswordTrait $this */
            return $this->canPasswordBeExpired()
                ? $this->passwordExpiresAt === null || $this->passwordExpiresAt > time()
                : true;
        }

        return true;
    }

    /**
     * @see \Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        if (in_array(LockAccountTrait::class, class_uses($this), true)) {
            /** @var LockAccountTrait $this */
            return $this->canAccountBeLocked()
                ? $this->lockedUntil === null || $this->lockedUntil !== 0 && $this->lockedUntil <= time()
                : true;
        }

        return true;
    }
}
