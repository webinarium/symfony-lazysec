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
use Ramsey\Uuid\Uuid;

class ResetPasswordTraitTest extends TestCase
{
    public function testResetToken()
    {
        $user = new DummyUserEx();

        $token = $user->generateResetToken(new \DateInterval('PT1M'));
        self::assertRegExp('/^([0-9a-f]{32}$)/', $token);
        self::assertTrue($user->isResetTokenValid($token));
        self::assertFalse($user->isResetTokenValid(Uuid::uuid4()->getHex()));

        $user->clearResetToken();
        self::assertFalse($user->isResetTokenValid($token));

        $token = $user->generateResetToken(new \DateInterval('PT0M'));
        self::assertFalse($user->isResetTokenValid($token));
    }
}
