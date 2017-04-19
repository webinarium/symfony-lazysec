<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Tests\Authenticator;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Pignus\Authenticator\AbstractOAuth2Authenticator;
use Pignus\Tests\Model\DummyUser;
use Symfony\Component\Routing\RouterInterface;

class DummyOAuth2Authenticator extends AbstractOAuth2Authenticator
{
    protected $provider;

    /**
     * {@inheritdoc}
     */
    protected function getProvider(RouterInterface $router, string $firewall)
    {
        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserFromResourceOwner(ResourceOwnerInterface $owner)
    {
        if ($owner->getId() === 123) {
            $user = new DummyUser();

            $reflection = new \ReflectionProperty(DummyUser::class, 'username');
            $reflection->setAccessible(true);
            $reflection->setValue($user, 'dummy');

            return $user;
        }

        return null;
    }
}
