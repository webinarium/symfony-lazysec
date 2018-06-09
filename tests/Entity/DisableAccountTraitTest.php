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

use PHPUnit\Framework\TestCase;

class DisableAccountTraitTest extends TestCase
{
    public function testSetEnabled()
    {
        $user = new DummyUser();
        self::assertTrue($user->isEnabled());

        $user->setEnabled(false);
        self::assertFalse($user->isEnabled());

        $user->setEnabled(true);
        self::assertTrue($user->isEnabled());
    }
}
