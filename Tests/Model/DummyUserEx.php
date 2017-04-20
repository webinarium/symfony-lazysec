<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Tests\Model;

use Pignus\Model\DisableAccountTrait;
use Pignus\Model\ExpireAccountTrait;

class DummyUserEx extends DummyUser
{
    use DisableAccountTrait;
    use ExpireAccountTrait;
}
