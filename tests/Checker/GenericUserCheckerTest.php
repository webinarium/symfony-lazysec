<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2018 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace LazySec\Tests\Checker;

use LazySec\Checker\GenericUserChecker;
use LazySec\Tests\Entity\DummyUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;

class GenericUserCheckerTest extends TestCase
{
    /** @var DummyUser */
    protected $user;

    /** @var GenericUserChecker */
    protected $checker;

    protected function setUp()
    {
        parent::setUp();

        $this->user    = new DummyUser();
        $this->checker = new GenericUserChecker();
    }

    public function testPreAuthSuccess()
    {
        $this->checker->checkPreAuth($this->user);
    }

    public function testPreAuthLockedException()
    {
        $this->expectException(LockedException::class);
        $this->expectExceptionMessage('User account is locked.');

        $this->user->lockAccount();
        $this->checker->checkPreAuth($this->user);
    }

    public function testPreAuthDisabledException()
    {
        $this->expectException(DisabledException::class);
        $this->expectExceptionMessage('User account is disabled.');

        $this->user->setEnabled(false);
        $this->checker->checkPreAuth($this->user);
    }

    public function testPreAuthAccountExpiredException()
    {
        $this->expectException(AccountExpiredException::class);
        $this->expectExceptionMessage('User account has expired.');

        $this->user->expireAccountAt(date_create()->sub(new \DateInterval('PT1M')));
        $this->checker->checkPreAuth($this->user);
    }

    public function testPostAuthSuccess()
    {
        $this->checker->checkPostAuth($this->user);
    }

    public function testPostAuthCredentialsExpiredException()
    {
        $this->expectException(CredentialsExpiredException::class);
        $this->expectExceptionMessage('User credentials have expired.');

        $this->user->expirePasswordAt(date_create()->sub(new \DateInterval('PT1M')));
        $this->checker->checkPostAuth($this->user);
    }
}
