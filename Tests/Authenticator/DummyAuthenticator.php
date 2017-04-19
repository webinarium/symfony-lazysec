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

use Pignus\Authenticator\AbstractAuthenticator;
use Symfony\Component\Routing\RouterInterface;

class DummyAuthenticator extends AbstractAuthenticator
{
    /**
     * {@inheritdoc}
     */
    protected function getLoginUrl(RouterInterface $router, string $firewall): string
    {
        return $firewall === 'main' ? $router->generate('login') : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultUrl(RouterInterface $router, string $firewall): string
    {
        return $firewall === 'main' ? $router->generate('homepage') : '';
    }
}
