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
 * Trait for "lock account" feature.
 */
trait LockAccountTrait
{
    /**
     * @var int Number of consecutive unsuccessful attempts to authenticate.
     *
     * @ORM\Column(name="auth_failures", type="integer", nullable=true)
     */
    protected $authFailures;

    /**
     * @var int Unix Epoch timestamp which the account is locked till.
     *          When zero, the account is considered as locked for permanent.
     *
     * @ORM\Column(name="locked_until", type="integer", nullable=true)
     */
    protected $lockedUntil;

    /**
     * Increases number of authentication failures.
     *
     * @return null|int New authentication failures number.
     */
    public function incAuthFailures(): ?int
    {
        return $this->canAccountBeLocked() ? ++$this->authFailures : null;
    }

    /**
     * Locks the account until specified moment of time (NULL for permanent lock).
     *
     * @param \DateTime $time
     *
     * @return self
     */
    public function lockAccount(\DateTime $time = null): self
    {
        if ($this->canAccountBeLocked()) {
            $this->authFailures = null;
            $this->lockedUntil  = $time === null ? 0 : $time->getTimestamp();
        }

        return $this;
    }

    /**
     * Unlocks the account.
     *
     * @return self
     */
    public function unlockAccount(): self
    {
        $this->authFailures = null;
        $this->lockedUntil  = null;

        return $this;
    }

    /**
     * Specifies whether the "lock account" feature is available for this user.
     *
     * @return bool
     */
    protected function canAccountBeLocked(): bool
    {
        return true;
    }
}
