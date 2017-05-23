<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Authenticator;

use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Authenticates user against the database.
 */
abstract class AbstractAuthenticator extends AbstractGuardAuthenticator
{
    protected $router;
    protected $session;
    protected $encoders;
    protected $firewalls;

    /**
     * Dependency Injection constructor.
     *
     * @param RouterInterface         $router
     * @param SessionInterface        $session
     * @param EncoderFactoryInterface $encoders
     * @param FirewallMap             $firewalls
     */
    public function __construct(
        RouterInterface         $router,
        SessionInterface        $session,
        EncoderFactoryInterface $encoders,
        FirewallMap             $firewalls)
    {
        $this->router    = $router;
        $this->session   = $session;
        $this->encoders  = $encoders;
        $this->firewalls = $firewalls;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        // Do not redirect the user if it's an AJAX request.
        if ($request->isXmlHttpRequest()) {

            $exception = $this->session->get(Security::AUTHENTICATION_ERROR);
            $this->session->remove(Security::AUTHENTICATION_ERROR);

            $message = $exception === null
                ? 'Authentication required.'
                : $exception->getMessage();

            return new Response($message, Response::HTTP_UNAUTHORIZED);
        }

        $firewall = $this->firewalls->getFirewallConfig($request)->getName();

        return new RedirectResponse($this->getLoginUrl($this->router, $firewall));
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        if (!$request->request->has('_username')) {
            return null;
        }

        return [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            return $userProvider->loadUserByUsername($credentials['username']);
        }
        catch (UsernameNotFoundException $e) {
            throw new AuthenticationException('Bad credentials.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $encoder = $this->encoders->getEncoder($user);

        if (!$encoder->isPasswordValid($user->getPassword(), $credentials['password'], $user->getSalt())) {
            throw new AuthenticationException('Bad credentials.');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $this->session->remove(Security::AUTHENTICATION_ERROR);

        // An URL the user was trying to reach before authentication.
        $targetPath = sprintf('_security.%s.target_path', $providerKey);
        $targetUrl  = $this->session->get($targetPath, $this->getDefaultUrl($this->router, $providerKey));

        // Do not redirect the user if it's an AJAX request.
        return $request->isXmlHttpRequest()
            ? new JsonResponse()
            : new RedirectResponse($targetUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->session->set(Security::AUTHENTICATION_ERROR, $exception);

        return $this->start($request, $exception);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return true;
    }

    /**
     * Returns URL to login page of the specified firewall.
     *
     * @param RouterInterface $router
     * @param string          $firewall
     *
     * @return string
     */
    abstract protected function getLoginUrl(RouterInterface $router, string $firewall): string;

    /**
     * Returns URL to default page of the specified firewall.
     *
     * @param RouterInterface $router
     * @param string          $firewall
     *
     * @return string
     */
    abstract protected function getDefaultUrl(RouterInterface $router, string $firewall): string;
}
