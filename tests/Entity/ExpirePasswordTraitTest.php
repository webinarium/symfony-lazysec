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

class ExpirePasswordTraitTest extends TestCase
{
    public function testExpirePasswordAt()
    {
        $now = new \DateTime();

        $user = new DummyUser();
        self::assertTrue($user->isCredentialsNonExpired());

        $user->expirePasswordAt($now->sub(new \DateInterval('PT1M')));
        self::assertFalse($user->isCredentialsNonExpired());

        $user->expirePasswordAt(null);
        self::assertTrue($user->isCredentialsNonExpired());

        $user->expirePasswordAt($now->add(new \DateInterval('PT2M')));
        self::assertTrue($user->isCredentialsNonExpired());
    }

    public function testExpirePasswordIn()
    {
        $user = new DummyUser();
        self::assertTrue($user->isCredentialsNonExpired());

        $user->expirePasswordIn(new \DateInterval('PT0M'));
        self::assertFalse($user->isCredentialsNonExpired());

        $user->expirePasswordIn(new \DateInterval('PT1M'));
        self::assertTrue($user->isCredentialsNonExpired());
    }
}
