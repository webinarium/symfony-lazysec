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

use Pignus\EventListener\UnauthorizedRequest;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

class UnauthorizedRequestTest extends \PHPUnit_Framework_TestCase
{
    /** @var RouterInterface */
    protected $router;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var AuthenticationUtils */
    protected $authenticationUtils;

    /** @var FirewallMap */
    protected $firewallMap;

    protected function setUp()
    {
        parent::setUp();

        $this->router = $this->createMock(RouterInterface::class);
        $this->router
            ->method('generate')
            ->willReturnMap([
                ['pignus.login', [], RouterInterface::ABSOLUTE_URL, 'http://localhost/login'],
                ['pignus.login', [], RouterInterface::ABSOLUTE_PATH, '/login'],
            ]);

        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator
            ->method('trans')
            ->willReturnMap([['Authentication required.', [], null, null, 'Error: authentication required.']]);

        $this->authenticationUtils = $this->createMock(AuthenticationUtils::class);

        $this->firewallMap = $this->createMock(FirewallMap::class);
        $this->firewallMap
            ->method('getFirewallConfig')
            ->willReturn(new FirewallConfig('main', ''));
    }

    public function testSuccessfulHttpRequest()
    {
        /** @var HttpKernelInterface $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);

        $request  = new Request();
        $response = new Response('Test');

        $event = new FilterResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $listener = new UnauthorizedRequest($this->router, $this->translator, $this->authenticationUtils, $this->firewallMap, [
            'main' => 'pignus.login',
        ]);

        $listener->onResponse($event);

        self::assertEquals(Response::HTTP_OK, $event->getResponse()->getStatusCode());
        self::assertEquals('Test', $event->getResponse()->getContent());
    }

    public function testSuccessfulAjaxRequest()
    {
        /** @var HttpKernelInterface $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);

        $request  = new Request();
        $response = new Response('Test');

        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $event = new FilterResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $listener = new UnauthorizedRequest($this->router, $this->translator, $this->authenticationUtils, $this->firewallMap, [
            'main' => 'pignus.login',
        ]);

        $listener->onResponse($event);

        self::assertEquals(Response::HTTP_OK, $event->getResponse()->getStatusCode());
        self::assertEquals('Test', $event->getResponse()->getContent());
    }

    public function testUnauthorizedAjaxRequest()
    {
        /** @var HttpKernelInterface $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);

        $request  = new Request();
        $response = new Response('Test');

        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $response->headers->set('Location', 'http://localhost/login');
        $response->setStatusCode(Response::HTTP_FOUND);

        $event = new FilterResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $listener = new UnauthorizedRequest($this->router, $this->translator, $this->authenticationUtils, $this->firewallMap, [
            'main' => 'pignus.login',
        ]);

        $listener->onResponse($event);

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $event->getResponse()->getStatusCode());
        self::assertEquals('Error: authentication required.', $event->getResponse()->getContent());
    }

    public function testAjaxRequestUnknownFirewall()
    {
        /** @var HttpKernelInterface $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);

        $request  = new Request();
        $response = new Response('Test');

        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $response->headers->set('Location', 'http://localhost/login');
        $response->setStatusCode(Response::HTTP_FOUND);

        $event = new FilterResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $listener = new UnauthorizedRequest($this->router, $this->translator, $this->authenticationUtils, $this->firewallMap, [
            'admin' => 'pignus.login',
        ]);

        $listener->onResponse($event);

        self::assertEquals(Response::HTTP_FOUND, $event->getResponse()->getStatusCode());
        self::assertEquals('Test', $event->getResponse()->getContent());
    }
}
