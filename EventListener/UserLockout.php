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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Locks/unlocks user in accordance with the authentication attempt.
 */
class UserLockout implements EventSubscriberInterface
{
    protected $logger;
    protected $manager;
    protected $provider;
    protected $auth_failures;
    protected $lock_duration;

    /**
     * Dependency Injection constructor.
     *
     * @param LoggerInterface       $logger
     * @param ObjectManager         $manager
     * @param UserProviderInterface $provider
     * @param int                   $auth_failures
     * @param int                   $lock_duration
     */
    public function __construct(
        LoggerInterface       $logger,
        ObjectManager         $manager,
        UserProviderInterface $provider,
        int                   $auth_failures = null,
        int                   $lock_duration = null)
    {
        $this->logger        = $logger;
        $this->manager       = $manager;
        $this->provider      = $provider;
        $this->auth_failures = $auth_failures;
        $this->lock_duration = $lock_duration;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onSuccess',
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onFailure',
        ];
    }

    /**
     * Callback for successful authentication event.
     *
     * @param AuthenticationEvent $event
     */
    public function onSuccess(AuthenticationEvent $event)
    {
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
        $token = $event->getAuthenticationToken();

        $credentials = $token->getCredentials();
        $username    = $token->getUsername() ?: ($credentials['username'] ?? null);

        if ($username && $this->auth_failures !== null) {

            try {
                /** @var LockAccountTrait $user */
                $user = $this->provider->loadUserByUsername($username);

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
            catch (UsernameNotFoundException $e) {
            }
        }
    }
}
