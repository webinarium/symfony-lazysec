<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Tests\DependencyInjection;

use Pignus\DependencyInjection\PignusExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UnauthorizedRequestEventListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testListenerEmpty()
    {
        $config = [];

        $container = new ContainerBuilder();

        self::assertFalse($container->has('pignus.event_listener.unauthorized_request'));

        $extension = new PignusExtension();
        $extension->load(['pignus' => $config], $container);

        self::assertFalse($container->has('pignus.event_listener.unauthorized_request'));
    }

    public function testListenerFalse()
    {
        $config = [
            'unauthorized_request' => false,
        ];

        $container = new ContainerBuilder();

        self::assertFalse($container->has('pignus.event_listener.unauthorized_request'));

        $extension = new PignusExtension();
        $extension->load(['pignus' => $config], $container);

        self::assertFalse($container->has('pignus.event_listener.unauthorized_request'));
    }

    public function testListenerTrue()
    {
        $config = [
            'unauthorized_request' => true,
        ];

        $container = new ContainerBuilder();

        self::assertFalse($container->has('pignus.event_listener.unauthorized_request'));

        $extension = new PignusExtension();
        $extension->load(['pignus' => $config], $container);

        self::assertTrue($container->has('pignus.event_listener.unauthorized_request'));
    }
}
