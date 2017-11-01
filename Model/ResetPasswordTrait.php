<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Model;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Trait for "reset password" feature.
 */
trait ResetPasswordTrait
{
    /**
     * @var string Reset token.
     *
     * @ORM\Column(name="reset_token", type="string", length=32, nullable=true)
     */
    protected $resetToken;

    /**
     * @var int Unix Epoch timestamp when the reset token expires.
     *
     * @ORM\Column(name="reset_token_expires_at", type="integer", nullable=true)
     */
    protected $resetTokenExpiresAt;

    /**
     * Generates new reset token which expires in specified period of time.
     *
     * @param \DateInterval $interval
     *
     * @return string Generated token.
     */
    public function generateResetToken(\DateInterval $interval)
    {
        $now = new \DateTime();

        $this->resetToken          = Uuid::uuid4()->getHex();
        $this->resetTokenExpiresAt = $now->add($interval)->getTimestamp();

        return $this->resetToken;
    }

    /**
     * Clears current reset token.
     *
     * @return self
     */
    public function clearResetToken()
    {
        $this->resetToken          = null;
        $this->resetTokenExpiresAt = null;

        return $this;
    }

    /**
     * Checks whether specified reset token is valid.
     *
     * @param string $token
     *
     * @return bool
     */
    public function isResetTokenValid(string $token)
    {
        return
            $this->resetToken === $token        &&
            $this->resetTokenExpiresAt !== null &&
            $this->resetTokenExpiresAt > time();
    }
}
