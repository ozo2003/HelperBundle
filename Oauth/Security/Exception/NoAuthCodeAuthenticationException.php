<?php

namespace Sludio\HelperBundle\Oauth\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class NoAuthCodeAuthenticationException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'error_oauth_client_authorization_failed';
    }
}
