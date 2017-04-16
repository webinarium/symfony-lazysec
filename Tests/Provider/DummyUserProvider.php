<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Tests\Provider;

use Pignus\Provider\AbstractUserProvider;
use Pignus\Tests\Model\DummyUser;

class DummyUserProvider extends AbstractUserProvider
{
    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === DummyUser::class;
    }
}
