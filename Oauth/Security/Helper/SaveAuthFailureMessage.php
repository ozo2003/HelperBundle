<?php

namespace Sludio\HelperBundle\Oauth\Security\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

trait SaveAuthFailureMessage
{
    protected function saveAuthenticationErrorToSession(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
    }
}
