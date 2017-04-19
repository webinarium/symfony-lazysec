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
use Pignus\Tests\Model\DummyUser;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AbstractAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractAuthenticator */
    protected $authenticator;

    /** @var UserInterface */
    protected $user;

    protected function setUp()
    {
        parent::setUp();

        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('generate')
            ->willReturnMap([
                ['homepage', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/'],
                ['login', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/login'],
            ]);

        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('get')
            ->willReturnMap([
                [Security::AUTHENTICATION_ERROR, null, null],
                ['_security.main.target_path', '/', 'http://localhost/profile'],
            ]);

        $encoder = $this->createMock(PasswordEncoderInterface::class);
        $encoder
            ->method('isPasswordValid')
            ->willReturnMap([
                ['secret', 'secret', null, true],
            ]);

        $encoders = $this->createMock(EncoderFactoryInterface::class);
        $encoders
            ->method('getEncoder')
            ->willReturn($encoder);

        $firewallMap = $this->createMock(FirewallMap::class);
        $firewallMap
            ->method('getFirewallConfig')
            ->willReturn(new FirewallConfig('main', ''));

        /** @var RouterInterface $router */
        /** @var SessionInterface $session */
        /** @var EncoderFactoryInterface $encoders */
        /** @var FirewallMap $firewallMap */
        $this->authenticator = new DummyAuthenticator(
            $router,
            $session,
            $encoders,
            $firewallMap
        );

        $this->user = $this->createMock(UserInterface::class);
        $this->user
            ->method('getPassword')
            ->willReturn('secret');
    }

    public function testStart()
    {
        $request = new Request();

        $response = $this->authenticator->start($request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertEquals('/login', $response->headers->get('location'));
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
            'username' => 'admin',
            'password' => 'secret',
        ];

        $request = new Request([], [
            '_username' => 'admin',
            '_password' => 'secret',
        ]);

        self::assertEquals($expected, $this->authenticator->getCredentials($request));
    }

    public function testGetCredentialsMissing()
    {
        $request = new Request();

        self::assertNull($this->authenticator->getCredentials($request));
    }

    public function testGetUserSuccess()
    {
        $credentials = [
            'username' => 'admin',
            'password' => 'secret',
        ];

        $provider = $this->createMock(UserProviderInterface::class);
        $provider
            ->method('loadUserByUsername')
            ->with('admin')
            ->willReturn($this->user);

        /** @var UserProviderInterface $provider */
        self::assertEquals($this->user, $this->authenticator->getUser($credentials, $provider));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage Bad credentials.
     */
    public function testGetUserNotFound()
    {
        $credentials = [
            'username' => 'unknown',
            'password' => 'secret',
        ];

        $provider = $this->createMock(UserProviderInterface::class);
        $provider
            ->method('loadUserByUsername')
            ->with('unknown')
            ->willThrowException(new UsernameNotFoundException('Not found.'));

        /** @var UserProviderInterface $provider */
        $this->authenticator->getUser($credentials, $provider);
    }

    public function testCheckCredentialsSuccess()
    {
        $credentials = [
            'username' => 'admin',
            'password' => 'secret',
        ];

        self::assertTrue($this->authenticator->checkCredentials($credentials, $this->user));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @expectedExceptionMessage Bad credentials.
     */
    public function testCheckCredentialsFailure()
    {
        $credentials = [
            'username' => 'admin',
            'password' => 'wrong',
        ];

        $this->authenticator->checkCredentials($credentials, $this->user);
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

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertEquals([], json_decode($response->getContent(), true));
    }

    public function testOnAuthenticationFailure()
    {
        $request   = new Request();
        $exception = new AuthenticationException('Bad credentials.');

        $response = $this->authenticator->onAuthenticationFailure($request, $exception);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertEquals('/login', $response->headers->get('location'));
    }

    public function testOnAuthenticationFailureAjax()
    {
        $exception = new AuthenticationException('Bad credentials.');

        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('get')
            ->willReturnMap([
                [Security::AUTHENTICATION_ERROR, null, $exception],
                ['_security.main.target_path', '/home', 'http://localhost/profile'],
            ]);

        $reflection = new \ReflectionProperty(DummyAuthenticator::class, 'session');
        $reflection->setAccessible(true);
        $reflection->setValue($this->authenticator, $session);

        $request = new Request();
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->authenticator->onAuthenticationFailure($request, $exception);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertEquals('Bad credentials.', $response->getContent());
    }

    public function testSupportsRememberMe()
    {
        self::assertTrue($this->authenticator->supportsRememberMe());
    }
}
