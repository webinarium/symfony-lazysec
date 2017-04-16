<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Tests\Controller;

use Pignus\Controller\LoginController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testLoginActionAnonymous()
    {
        $controller = new LoginController();
        $controller->setContainer($this->createContainer());

        $response = $controller->indexAction();

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertContains('Login page.', $response->getContent());
    }

    /**
     * @expectedException \Pignus\Exception\UserAuthenticatedException
     * @expectedExceptionMessage Access Denied.
     */
    public function testLoginActionAuthorized()
    {
        $controller = new LoginController();
        $controller->setContainer($this->createContainer(true));

        $controller->indexAction();
    }

    /**
     * Creates mocked DI-container.
     *
     * @param bool $authorized Whether to simulate an authorized user.
     *
     * @return ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createContainer(bool $authorized = false)
    {
        // Service: 'security.authentication_utils'
        $authenticationUtils = $this->createMock(AuthenticationUtils::class);

        // Service: 'security.authorization_checker'
        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willReturn($authorized);

        // Service: 'twig'
        $twig = $this->createMock(DummyRenderer::class);
        $twig
            ->method('render')
            ->with('PignusBundle::login.html.twig')
            ->willReturn(new Response('Login page.'));

        // Container
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->method('has')
            ->willReturnMap([
                ['security.authorization_checker', true],
                ['security.authentication_utils', true],
                ['twig', true],
            ]);

        $container
            ->method('get')
            ->willReturnMap([
                ['security.authorization_checker', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $authorizationChecker],
                ['security.authentication_utils', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $authenticationUtils],
                ['twig', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $twig],
            ]);

        return $container;
    }
}
