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

class LoginParametersTest extends \PHPUnit_Framework_TestCase
{
    public function testParameterEmpty()
    {
        $config = [];

        $container = new ContainerBuilder();

        self::assertFalse($container->hasParameter('pignus.login'));

        $extension = new PignusExtension();
        $extension->load(['pignus' => $config], $container);

        self::assertTrue($container->hasParameter('pignus.login'));
        self::assertEquals([], $container->getParameter('pignus.login'));
    }

    public function testParameterPresent()
    {
        $expected = [
            'main'  => 'default_login_page',
            'admin' => 'admin_login_page',
        ];

        $config = [
            'login' => [
                'main'  => 'default_login_page',
                'admin' => 'admin_login_page',
            ],
        ];

        $container = new ContainerBuilder();

        self::assertFalse($container->hasParameter('pignus.login'));

        $extension = new PignusExtension();
        $extension->load(['pignus' => $config], $container);

        self::assertTrue($container->hasParameter('pignus.login'));
        self::assertEquals($expected, $container->getParameter('pignus.login'));
    }
}
