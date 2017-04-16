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

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * User repository interface.
 */
interface UserRepositoryInterface extends ObjectRepository
{
    /**
     * Finds user by username.
     *
     * @param string $username
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface|null
     */
    public function findOneByUsername(string $username);
}
