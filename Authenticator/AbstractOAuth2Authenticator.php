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

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Authenticates user against OAuth2 server.
 */
abstract class AbstractOAuth2Authenticator extends AbstractGuardAuthenticator
{
    protected $router;
    protected $session;
    protected $firewalls;

    /**
     * Dependency Injection constructor.
     *
     * @param RouterInterface  $router
     * @param SessionInterface $session
     * @param FirewallMap      $firewalls
     */
    public function __construct(
        RouterInterface  $router,
        SessionInterface $session,
        FirewallMap      $firewalls
    )
    {
        $this->router    = $router;
        $this->session   = $session;
        $this->firewalls = $firewalls;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        // Do not redirect the user if it's an AJAX request.
        if ($request->isXmlHttpRequest() || $request->getContentType() === 'json') {
            return new Response('Authentication required.', Response::HTTP_UNAUTHORIZED);
        }

        $exception = $this->session->get(Security::AUTHENTICATION_ERROR);
        $this->session->remove(Security::AUTHENTICATION_ERROR);

        if ($exception !== null) {
            return new Response($exception->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        $firewall = $this->firewalls->getFirewallConfig($request)->getName();
        $statevar = $firewall . '@' . static::class;

        $provider = $this->getProvider($this->router, $firewall);
        $authUrl  = $provider->getAuthorizationUrl();

        $this->session->set($statevar, $provider->getState());

        return new RedirectResponse($authUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        $code  = $request->query->get('code');
        $state = $request->query->get('state');

        if (!$code && !$state) {
            return null;
        }

        if (!$code || !$state) {
            throw new AuthenticationException('Bad credentials.');
        }

        $firewall = $this->firewalls->getFirewallConfig($request)->getName();
        $statevar = $firewall . '@' . static::class;

        if ($state !== $this->session->get($statevar)) {
            $this->session->remove($statevar);

            return null;
        }

        return [
            'firewall' => $firewall,
            'code'     => $code,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $provider = $this->getProvider($this->router, $credentials['firewall']);

            $token = $provider->getAccessToken('authorization_code', [
                'code' => $credentials['code'],
            ]);

            $owner = $provider->getResourceOwner($token);
            $user  = $this->getUserFromResourceOwner($owner);

            if ($user === null) {
                throw new AuthenticationException('Bad credentials.');
            }

            return $user;
        }
        catch (\Exception $e) {
            throw new AuthenticationException($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
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
        $targetUrl  = $this->session->get($targetPath, '/');

        // Do not redirect the user if it's an AJAX request.
        return $request->isXmlHttpRequest() || $request->getContentType() === 'json'
            ? null
            : new RedirectResponse($targetUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->session->set(Security::AUTHENTICATION_ERROR, $exception);

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Returns OAuth2 provider for the specified firewall.
     *
     * @param RouterInterface $router
     * @param string          $firewall
     *
     * @return AbstractProvider
     */
    abstract protected function getProvider(RouterInterface $router, string $firewall): AbstractProvider;

    /**
     * Returns user entity based on specified OAuth2 account.
     *
     * @param ResourceOwnerInterface $owner
     *
     * @return null|UserInterface
     */
    abstract protected function getUserFromResourceOwner(ResourceOwnerInterface $owner);
}
