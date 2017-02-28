<?php

namespace Sludio\HelperBundle\Oauth\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class NoAuthCodeAuthenticationException extends AuthenticationException
{
    public function getMessageKey()
    {
        return 'Authentication failed! Did you authorize our app?';
    }
}
