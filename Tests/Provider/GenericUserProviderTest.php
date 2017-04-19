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

use Pignus\Model\UserRepositoryInterface;
use Pignus\Provider\GenericUserProvider;
use Pignus\Tests\Model\DummyUser;
use Symfony\Component\Security\Core\User\User;

class GenericUserProviderTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUnknownUserByUsername()
    {
        $this->provider->loadUserByUsername('unknown');
    }

    public function testRefreshUser()
    {
        $result = $this->provider->refreshUser($this->user);

        self::assertInstanceOf(DummyUser::class, $result);
        self::assertEquals('admin', $result->getUsername());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testRefreshUnknownUser()
    {
        $reflection = new \ReflectionProperty(DummyUser::class, 'username');
        $reflection->setAccessible(true);
        $reflection->setValue($this->user, 'unknown');

        $this->provider->refreshUser($this->user);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testUnsupportedUserException()
    {
        $user = new User('admin', 'secret');

        $this->provider->refreshUser($user);
    }
}
