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

/**
 * Trait for "disable account" feature.
 */
trait DisableAccountTrait
{
    /**
     * @var bool Whether account is enabled.
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    protected $isEnabled = true;

    /**
     * Disables or enables the account.
     *
     * @param bool $isEnabled New status of the account.
     *
     * @return self
     */
    public function setEnabled(bool $isEnabled)
    {
        if ($this->canAccountBeDisabled()) {
            $this->isEnabled = $isEnabled;
        }

        return $this;
    }

    /**
     * Specifies whether the "disable account" feature is available for this user.
     *
     * @return bool
     */
    protected function canAccountBeDisabled(): bool
    {
        return true;
    }
}
