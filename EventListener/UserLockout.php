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

use Doctrine\Common\Persistence\ObjectManager;
use Pignus\Model\LockAccountTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Locks/unlocks user in accordance with the authentication attempt.
 */
class UserLockout implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $logger;
    protected $request_stack;
    protected $firewalls;
    protected $manager;
    protected $auth_failures;
    protected $lock_duration;

    /**
     * Dependency Injection constructor.
     *
     * @param LoggerInterface $logger
     * @param RequestStack    $request_stack
     * @param FirewallMap     $firewalls
     * @param ObjectManager   $manager
     * @param int             $auth_failures
     * @param int             $lock_duration
     */
    public function __construct(
        LoggerInterface       $logger,
        RequestStack          $request_stack,
        FirewallMap           $firewalls,
        ObjectManager         $manager,
        int                   $auth_failures = null,
        int                   $lock_duration = null)
    {
        $this->logger        = $logger;
        $this->request_stack = $request_stack;
        $this->firewalls     = $firewalls;
        $this->manager       = $manager;
        $this->auth_failures = $auth_failures;
        $this->lock_duration = $lock_duration;
    }

    /**
     * Callback for successful authentication event.
     *
     * @param AuthenticationEvent $event
     */
    public function onSuccess(AuthenticationEvent $event)
    {
        if ($this->auth_failures === null) {
            return;
        }

        $token = $event->getAuthenticationToken();

        /** @var LockAccountTrait $user */
        $user = $token->getUser();

        if ($user instanceof UserInterface && in_array(LockAccountTrait::class, class_uses($user), true)) {

            $this->logger->info('Authentication success', [$user->getUsername()]);

            $user->unlockAccount();

            $this->manager->persist($user);
            $this->manager->flush();
        }
    }

    /**
     * Callback for failed authentication event.
     *
     * @param AuthenticationFailureEvent $event
     */
    public function onFailure(AuthenticationFailureEvent $event)
    {
        if ($this->auth_failures === null) {
            return;
        }

        // Retrieve failed username.
        $token = $event->getAuthenticationToken();

        $credentials = $token->getCredentials();
        $username    = $token->getUsername() ?: ($credentials['username'] ?? null);

        // Retrieve current user provider.
        $provider = null;

        if ($request = $this->request_stack->getCurrentRequest()) {
            if ($config = $this->firewalls->getFirewallConfig($request)) {
                if ($serviceId = $config->getProvider()) {
                    $provider = $this->container->get($serviceId);
                }
            }
        }

        // Try to fetch user entity.
        if ($username !== null && $provider !== null) {

            try {
                /** @var LockAccountTrait $user */
                $user = $provider->loadUserByUsername($username);
            }
            catch (UsernameNotFoundException $e) {
                $user = null;
            }

            if ($user instanceof UserInterface && in_array(LockAccountTrait::class, class_uses($user), true)) {

                $this->logger->info('Authentication failure', [$username]);

                if ($user->incAuthFailures() >= $this->auth_failures) {

                    if ($this->lock_duration === null) {
                        $user->lockAccount();
                    }
                    else {
                        $interval = sprintf('PT%dM', $this->lock_duration);
                        $user->lockAccount(date_create()->add(new \DateInterval($interval)));
                    }
                }

                $this->manager->persist($user);
                $this->manager->flush();
            }
        }
    }
}
