<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace LazySec\Tests\Provider;

use LazySec\Provider\GenericUserProvider;
use LazySec\Repository\UserRepositoryInterface;
use LazySec\Tests\Entity\DummyUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;

class GenericUserProviderTest extends TestCase
{
    /** @var DummyUser */
    protected $user;

    /** @var GenericUserProvider */
    protected $provider;

    protected function setUp()
    {
        parent::setUp();

        $this->user = new DummyUser();

        $reflection = new \ReflectionProperty(DummyUser::class, 'username');
        $reflection->setAccessible(true);
        $reflection->setValue($this->user, 'admin');

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository
            ->method('findOneByUsername')
            ->willReturnMap([
                ['admin', $this->user],
            ]);

        $repository
            ->method('getClassName')
            ->willReturn(DummyUser::class);

        /** @var UserRepositoryInterface $repository */
        $this->provider = new GenericUserProvider($repository);
    }

    public function testLoadUserByUsername()
    {
        $result = $this->provider->loadUserByUsername('admin');

        self::assertInstanceOf(DummyUser::class, $result);
        self::assertEquals('admin', $result->getUsername());
    }

    public function testLoadUnknownUserByUsername()
    {
        $this->expectException(UsernameNotFoundException::class);

        $this->provider->loadUserByUsername('unknown');
    }

    public function testRefreshUser()
    {
        $result = $this->provider->refreshUser($this->user);

        self::assertInstanceOf(DummyUser::class, $result);
        self::assertEquals('admin', $result->getUsername());
    }

    public function testRefreshUnknownUser()
    {
        $this->expectException(UsernameNotFoundException::class);

        $reflection = new \ReflectionProperty(DummyUser::class, 'username');
        $reflection->setAccessible(true);
        $reflection->setValue($this->user, 'unknown');

        $this->provider->refreshUser($this->user);
    }

    public function testUnsupportedUserException()
    {
        $this->expectException(UnsupportedUserException::class);

        $user = new User('admin', 'secret');

        $this->provider->refreshUser($user);
    }
}
