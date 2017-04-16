<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\EventListener;

use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provides special response in case of unauthorized AJAX request.
 */
class UnauthorizedRequest
{
    protected $router;
    protected $translator;
    protected $utils;
    protected $firewalls;
    protected $routes;

    /**
     * Dependency Injection constructor.
     *
     * @param RouterInterface     $router
     * @param TranslatorInterface $translator
     * @param AuthenticationUtils $utils
     * @param FirewallMap         $firewalls
     * @param string[]            $routes
     */
    public function __construct(
        RouterInterface     $router,
        TranslatorInterface $translator,
        AuthenticationUtils $utils,
        FirewallMap         $firewalls,
        array               $routes)
    {
        $this->router     = $router;
        $this->translator = $translator;
        $this->utils      = $utils;
        $this->firewalls  = $firewalls;
        $this->routes     = $routes;
    }

    /**
     * Overrides the response if user is redirected to login page and it was an AJAX request.
     *
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        $firewall = $this->firewalls->getFirewallConfig($request)->getName();

        if (!array_key_exists($firewall, $this->routes)) {
            return;
        }

        $url = $this->router->generate($this->routes[$firewall], [], RouterInterface::ABSOLUTE_URL);

        if ($request->isXmlHttpRequest() && $response->isRedirect($url)) {

            $error   = $this->utils->getLastAuthenticationError();
            $message = $this->translator->trans($error === null ? 'Authentication required.' : $error->getMessage());

            $event->setResponse(new Response($message, Response::HTTP_UNAUTHORIZED));
        }
    }
}
