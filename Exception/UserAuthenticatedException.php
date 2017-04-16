<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Exception to be raised when authenticated user is trying to access a page addressed to anonymous users only.
 */
class UserAuthenticatedException extends AccessDeniedException
{
}
