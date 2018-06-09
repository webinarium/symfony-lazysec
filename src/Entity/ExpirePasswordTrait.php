<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017-2018 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace LazySec\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait for "password expiration" feature.
 */
trait ExpirePasswordTrait
{
    /**
     * @var int Unix Epoch timestamp when the password expires.
     *
     * @ORM\Column(name="password_expires_at", type="integer", nullable=true)
     */
    protected $passwordExpiresAt;

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Inherited from '\Symfony\Component\Security\Core\User\AdvancedUserInterface'.
     *
     * @return bool TRUE if the user's credentials are non expired, FALSE otherwise.
     */
    public function isCredentialsNonExpired(): bool
    {
        return $this->canPasswordBeExpired()
            ? $this->passwordExpiresAt === null || $this->passwordExpiresAt > time()
            : true;
    }

    /**
     * Makes password to expire at specified moment of time (NULL for no expiration).
     *
     * @param \DateTime $time
     *
     * @return self
     */
    public function expirePasswordAt(\DateTime $time = null): self
    {
        if ($this->canPasswordBeExpired()) {
            $this->passwordExpiresAt = $time === null ? null : $time->getTimestamp();
        }

        return $this;
    }

    /**
     * Makes password to expire in specified period of time.
     *
     * @param \DateInterval $interval
     *
     * @return self
     */
    public function expirePasswordIn(\DateInterval $interval): self
    {
        if ($this->canPasswordBeExpired()) {
            $this->passwordExpiresAt = date_create()->add($interval)->getTimestamp();
        }

        return $this;
    }

    /**
     * Specifies whether the "password expiration" feature is available for this user.
     *
     * @return bool
     */
    protected function canPasswordBeExpired(): bool
    {
        return true;
    }
}
