<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Tests\Model;

use Pignus\Model\UserTrait;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class DummyUser implements AdvancedUserInterface
{
    use UserTrait;

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
