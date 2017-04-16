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
 * @ORM\MappedSuperclass
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
        return true;
    }

    /**
     * @see \Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @see \Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @see \Symfony\Component\Security\Core\User\AdvancedUserInterface
     *
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }
}
