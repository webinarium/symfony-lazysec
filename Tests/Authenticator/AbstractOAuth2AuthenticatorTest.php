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

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Pignus\Authenticator\AbstractOAuth2Authenticator;
use Pignus\Tests\Model\DummyUser;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AbstractOAuth2AuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractOAuth2Authenticator */
    protected $authenticator;

    protected function setUp()
    {
        parent::setUp();

        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('generate')
            ->with('oauth2')
            ->willReturn('/oauth2');

        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('get')
            ->willReturnMap([
                ['main@Pignus\\Tests\\Authenticator\\DummyOAuth2Authenticator', null, 'goodState'],
                ['_security.main.target_path', '/', 'http://localhost/profile'],
            ]);

        $firewallMap = $this->createMock(FirewallMap::class);
        $firewallMap
            ->method('getFirewallConfig')
            ->willReturn(new FirewallConfig('main', ''));

        $goodOwner = new GenericResourceOwner(['id' => 123], 'id');
        $badOwner  = new GenericResourceOwner(['id' => null], 'id');

        $goodToken = new AccessToken(['access_token' => 'goodToken']);
        $badToken  = new AccessToken(['access_token' => 'badToken']);

        $provider = $this->createMock(AbstractProvider::class);
        $provider
            ->method('getAuthorizationUrl')
            ->willReturn('http://example.com/oauth2');
        $provider
            ->method('getState')
            ->willReturn('goodState');
        $provider
            ->method('getAccessToken')
            ->willReturnMap([
                ['authorization_code', ['code' => 'goodCode'], $goodToken],
                ['authorization_code', ['code' => 'badCode'], $badToken],
            ]);
        $provider
            ->method('getResourceOwner')
            ->willReturnMap([
                [$goodToken, $goodOwner],
                [$badToken, $badOwner],
            ]);

        /** @var RouterInterface $router */
        /** @var SessionInterface $session */
        /** @var FirewallMap $firewallMap */
        $this->authenticator = new DummyOAuth2Authenticator(
            $router,
            $session,
            $firewallMap
        );

        $reflection = new \ReflectionProperty(DummyOAuth2Authenticator::class, 'provider');
        $reflection->setAccessible(true);
        $reflection->setValue($this->authenticator, $provider);
    }

    public function testStart()
    {
        $request = new Request();

        $response = $this->authenticator->start($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertEquals('http://example.com/oauth2', $response->headers->get('location'));
    }

    public function testStartAjax()
    {
        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->authenticator->start($request);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertEquals('Authentication required.', $response->getContent());
    }

    public function testGetCredentialsSuccess()
    {
        $expected = [
            'firewall' => 'main',
            'code'     => 'goodCode',
        ];

        $request = new Request([
            'code'  => 'goodCode',
            'state' => 'goodState',
        ]);

        self::assertEquals($expected, $this->authenticator->getCredentials($request));
    }

    public function testGetCredentialsMissing()
    {
        $request = new Request();

        self::assertNull($this->authenticator->getCredentials($request));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage Bad credentials.
     */
    public function testGetCredentialsMissingCode()
    {
        $request = new Request([
            'state' => 'goodState',
        ]);

        $this->authenticator->getCredentials($request);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage Bad credentials.
     */
    public function testGetCredentialsMissingState()
    {
        $request = new Request([
            'code' => 'goodCode',
        ]);

        $this->authenticator->getCredentials($request);
    }

    public function testGetCredentialsWrongState()
    {
        $request = new Request([
            'code'  => 'goodCode',
            'state' => 'invalidState',
        ]);

        self::assertNull($this->authenticator->getCredentials($request));
    }

    public function testGetUserSuccess()
    {
        $credentials = [
            'firewall' => 'main',
            'code'     => 'goodCode',
        ];

        /** @var UserProviderInterface $provider */
        $provider = $this->createMock(UserProviderInterface::class);

        self::assertEquals('dummy', $this->authenticator->getUser($credentials, $provider)->getUsername());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage Bad credentials.
     */
    public function testGetUserNotFound()
    {
        $credentials = [
            'firewall' => 'main',
            'code'     => 'badCode',
        ];

        /** @var UserProviderInterface $provider */
        $provider = $this->createMock(UserProviderInterface::class);

        $this->authenticator->getUser($credentials, $provider)->getUsername();
    }

    public function testCheckCredentials()
    {
        self::assertTrue($this->authenticator->checkCredentials([], new DummyUser()));
    }

    public function testOnAuthenticationSuccess()
    {
        $token = $this->authenticator->createAuthenticatedToken(new DummyUser(), 'main');

        $request  = new Request();
        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'main');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertEquals('http://localhost/profile', $response->headers->get('Location'));
    }

    public function testOnAuthenticationSuccessAjax()
    {
        $token = $this->authenticator->createAuthenticatedToken(new DummyUser(), 'main');

        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'main');

        self::assertNull($response);
    }

    public function testOnAuthenticationFailure()
    {
        $request   = new Request();
        $exception = new AuthenticationException('Bad credentials.');

        $response = $this->authenticator->onAuthenticationFailure($request, $exception);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertEquals('Authentication required.', $response->getContent());
    }

    public function testSupportsRememberMe()
    {
        self::assertFalse($this->authenticator->supportsRememberMe());
    }
}
