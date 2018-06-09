<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace LazySec\Tests\Entity;

use LazySec\Entity\DisableAccountTrait;
use LazySec\Entity\ExpireAccountTrait;
use LazySec\Entity\ExpirePasswordTrait;
use LazySec\Entity\LockAccountTrait;
use LazySec\Entity\ResetPasswordTrait;
use LazySec\Entity\UserTrait;
use Symfony\Component\Security\Core\User\UserInterface;

class DummyUser implements UserInterface
{
    use UserTrait;
    use DisableAccountTrait;
    use ExpireAccountTrait;
    use ExpirePasswordTrait;
    use LockAccountTrait;
    use ResetPasswordTrait;

    protected $username;

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return [];
    }
}
