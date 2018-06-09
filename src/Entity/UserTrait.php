<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017-2018 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace LazySec\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User trait.
 *
 * @ORM\MappedSuperclass(repositoryClass="LazySec\Repository\UserRepositoryInterface")
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
}
