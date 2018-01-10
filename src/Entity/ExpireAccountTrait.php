<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace LazySec\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait for "account expiration" feature.
 */
trait ExpireAccountTrait
{
    /**
     * @var int Unix Epoch timestamp when the account expires.
     *
     * @ORM\Column(name="account_expires_at", type="integer", nullable=true)
     */
    protected $accountExpiresAt;

    /**
     * Makes account to expire at specified moment of time (NULL for no expiration).
     *
     * @param \DateTime $time
     *
     * @return self
     */
    public function expireAccountAt(\DateTime $time = null): self
    {
        if ($this->canAccountBeExpired()) {
            $this->accountExpiresAt = $time === null ? null : $time->getTimestamp();
        }

        return $this;
    }

    /**
     * Makes account to expire in specified period of time.
     *
     * @param \DateInterval $interval
     *
     * @return self
     */
    public function expireAccountIn(\DateInterval $interval): self
    {
        if ($this->canAccountBeExpired()) {
            $this->accountExpiresAt = date_create()->add($interval)->getTimestamp();
        }

        return $this;
    }

    /**
     * Specifies whether the "account expiration" feature is available for this user.
     *
     * @return bool
     */
    protected function canAccountBeExpired(): bool
    {
        return true;
    }
}
