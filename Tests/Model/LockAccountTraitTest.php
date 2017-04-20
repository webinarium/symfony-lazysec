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

class LockAccountTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testIncAuthFailures()
    {
        $user = new DummyUserEx();

        $reflection = new \ReflectionProperty(DummyUserEx::class, 'authFailures');
        $reflection->setAccessible(true);
        self::assertNull($reflection->getValue($user));

        self::assertEquals(1, $user->incAuthFailures());
        self::assertEquals(2, $user->incAuthFailures());
        self::assertEquals(3, $user->incAuthFailures());
    }

    public function testLockUnlockAccount()
    {
        $now = new \DateTime();

        $user = new DummyUserEx();
        self::assertTrue($user->isAccountNonLocked());

        $user->lockAccount($now->add(new \DateInterval('PT1M')));
        self::assertFalse($user->isAccountNonLocked());

        $user->lockAccount($now->sub(new \DateInterval('PT2M')));
        self::assertTrue($user->isAccountNonLocked());

        $user->lockAccount(null);
        self::assertFalse($user->isAccountNonLocked());

        $user->unlockAccount();
        self::assertTrue($user->isAccountNonLocked());
    }
}
