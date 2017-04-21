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

class ParametersTest extends \PHPUnit_Framework_TestCase
{
    public function testParametersEmpty()
    {
        $expected = [
            'pignus.login' => [],
        ];

        $config = [];

        $container = new ContainerBuilder();

        foreach ($expected as $key => $value) {
            self::assertFalse($container->hasParameter($key));
        }

        $extension = new PignusExtension();
        $extension->load(['pignus' => $config], $container);

        foreach ($expected as $key => $value) {
            self::assertTrue($container->hasParameter($key));
            self::assertEquals($expected[$key], $container->getParameter($key));
        }
    }

    public function testParametersPresent()
    {
        $expected = [
            'pignus.login' => [
                'main'  => 'default_login_page',
                'admin' => 'admin_login_page',
            ],
        ];

        $config = [
            'login' => [
                'main'  => 'default_login_page',
                'admin' => 'admin_login_page',
            ],
        ];

        $container = new ContainerBuilder();

        foreach ($expected as $key => $value) {
            self::assertFalse($container->hasParameter($key));
        }

        $extension = new PignusExtension();
        $extension->load(['pignus' => $config], $container);

        foreach ($expected as $key => $value) {
            self::assertTrue($container->hasParameter($key));
            self::assertEquals($value, $container->getParameter($key));
        }
    }
}
