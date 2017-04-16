<?php

//----------------------------------------------------------------------
//
//  Copyright (C) 2017 Artem Rodygin
//
//  You should have received a copy of the MIT License along with
//  this file. If not, see <http://opensource.org/licenses/MIT>.
//
//----------------------------------------------------------------------

namespace Pignus\Controller;

use Pignus\Exception\UserAuthenticatedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Login page.
 */
class LoginController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction(): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new UserAuthenticatedException();
        }

        /** @var \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $utils */
        $utils = $this->get('security.authentication_utils');

        return $this->render('PignusBundle::login.html.twig', [
            'username' => $utils->getLastUsername(),
            'error'    => $utils->getLastAuthenticationError(),
        ]);
    }
}
