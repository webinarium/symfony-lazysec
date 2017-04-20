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

class DisableAccountTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testSetEnabled()
    {
        $user = new DummyUserEx();
        self::assertTrue($user->isEnabled());

        $user->setEnabled(false);
        self::assertFalse($user->isEnabled());

        $user->setEnabled(true);
        self::assertTrue($user->isEnabled());
    }
}
