<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace LazySec\Tests\Entity;

use LazySec\Entity\DisableAccountTrait;
use LazySec\Entity\ExpireAccountTrait;
use LazySec\Entity\ExpirePasswordTrait;
use LazySec\Entity\LockAccountTrait;
use LazySec\Entity\ResetPasswordTrait;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class DummyUserEx extends DummyUser implements AdvancedUserInterface
{
    use DisableAccountTrait;
    use ExpireAccountTrait;
    use ExpirePasswordTrait;
    use LockAccountTrait;
    use ResetPasswordTrait;
}
