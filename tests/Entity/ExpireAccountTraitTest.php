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

class ExpireAccountTraitTest extends TestCase
{
    public function testExpireAccountAt()
    {
        $now = new \DateTime();

        $user = new DummyUser();
        self::assertTrue($user->isAccountNonExpired());

        $user->expireAccountAt($now->sub(new \DateInterval('PT1M')));
        self::assertFalse($user->isAccountNonExpired());

        $user->expireAccountAt(null);
        self::assertTrue($user->isAccountNonExpired());

        $user->expireAccountAt($now->add(new \DateInterval('PT2M')));
        self::assertTrue($user->isAccountNonExpired());
    }

    public function testExpireAccountIn()
    {
        $user = new DummyUser();
        self::assertTrue($user->isAccountNonExpired());

        $user->expireAccountIn(new \DateInterval('PT0M'));
        self::assertFalse($user->isAccountNonExpired());

        $user->expireAccountIn(new \DateInterval('PT1M'));
        self::assertTrue($user->isAccountNonExpired());
    }
}
