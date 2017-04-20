<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Tests\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Pignus\EventListener\UserLockout;
use Pignus\Tests\Model\DummyUserEx;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserLockoutTest extends \PHPUnit_Framework_TestCase
{
    /** @var DummyUserEx */
    protected $user;

    /** @var LoggerInterface */
    protected $logger;

    /** @var ObjectManager */
    protected $manager;

    /** @var UserProviderInterface */
    protected $provider;

    protected function setUp()
    {
        parent::setUp();

        $this->user = new DummyUserEx();

        $reflection = new \ReflectionProperty(DummyUserEx::class, 'username');
        $reflection->setAccessible(true);
        $reflection->setValue($this->user, 'admin');

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->manager = $this->createMock(ObjectManager::class);

        $this->provider = $this->createMock(UserProviderInterface::class);
        $this->provider
            ->method('loadUserByUsername')
            ->willReturnMap([
                ['admin', $this->user],
            ]);
    }

    public function testGetSubscribedEvents()
    {
        $expected = [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onSuccess',
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onFailure',
        ];

        self::assertEquals($expected, UserLockout::getSubscribedEvents());
    }

    public function testTemporaryLock()
    {
        $listener = new UserLockout(
            $this->logger,
            $this->manager,
            $this->provider,
            2, 30
        );

        $token   = new UsernamePasswordToken('admin', 'secret', 'main');
        $failure = new AuthenticationFailureEvent($token, new AuthenticationException());

        $listener->onFailure($failure);
        self::assertTrue($this->user->isAccountNonLocked());

        $listener->onFailure($failure);
        self::assertFalse($this->user->isAccountNonLocked());

        self::assertNotEquals(0, $this->getLockedUntil($this->user));
    }

    public function testPermanentLock()
    {
        $listener = new UserLockout(
            $this->logger,
            $this->manager,
            $this->provider,
            2, null
        );

        $token   = new UsernamePasswordToken('admin', 'secret', 'main');
        $failure = new AuthenticationFailureEvent($token, new AuthenticationException());

        $listener->onFailure($failure);
        self::assertTrue($this->user->isAccountNonLocked());

        $listener->onFailure($failure);
        self::assertFalse($this->user->isAccountNonLocked());

        self::assertEquals(0, $this->getLockedUntil($this->user));
    }

    public function testNoLock()
    {
        $listener = new UserLockout(
            $this->logger,
            $this->manager,
            $this->provider,
            null, null
        );

        $token   = new UsernamePasswordToken('admin', 'secret', 'main');
        $failure = new AuthenticationFailureEvent($token, new AuthenticationException());

        $listener->onFailure($failure);
        self::assertTrue($this->user->isAccountNonLocked());

        $listener->onFailure($failure);
        self::assertTrue($this->user->isAccountNonLocked());

        self::assertNull($this->getAuthFailures($this->user));
        self::assertNull($this->getLockedUntil($this->user));
    }

    public function testUserNotFound()
    {
        $provider = $this->createMock(UserProviderInterface::class);
        $provider
            ->method('loadUserByUsername')
            ->with('unknown')
            ->willThrowException(new UsernameNotFoundException());

        /** @var UserProviderInterface $provider */
        $listener = new UserLockout(
            $this->logger,
            $this->manager,
            $provider,
            2, 30
        );

        $token   = new UsernamePasswordToken('unknown', 'secret', 'main');
        $failure = new AuthenticationFailureEvent($token, new AuthenticationException());

        $listener->onFailure($failure);
        self::assertTrue($this->user->isAccountNonLocked());

        $listener->onFailure($failure);
        self::assertTrue($this->user->isAccountNonLocked());

        self::assertNull($this->getAuthFailures($this->user));
        self::assertNull($this->getLockedUntil($this->user));
    }

    public function testUnlock()
    {
        $listener = new UserLockout(
            $this->logger,
            $this->manager,
            $this->provider,
            2, 30
        );

        $token   = new UsernamePasswordToken($this->user, 'secret', 'main');
        $success = new AuthenticationEvent($token);

        $this->user->lockAccount();
        self::assertFalse($this->user->isAccountNonLocked());

        $listener->onSuccess($success);
        self::assertTrue($this->user->isAccountNonLocked());
    }

    protected function getAuthFailures(DummyUserEx $user)
    {
        $reflection = new \ReflectionProperty(DummyUserEx::class, 'authFailures');
        $reflection->setAccessible(true);

        return $reflection->getValue($user);
    }

    protected function getLockedUntil(DummyUserEx $user)
    {
        $reflection = new \ReflectionProperty(DummyUserEx::class, 'lockedUntil');
        $reflection->setAccessible(true);

        return $reflection->getValue($user);
    }
}
