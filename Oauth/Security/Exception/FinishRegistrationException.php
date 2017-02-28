<?php

namespace Sludio\HelperBundle\Oauth\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class FinishRegistrationException extends AuthenticationException
{
    private $userInformation;

    public function __construct($userInfo, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->userInformation = $userInfo;

        parent::__construct($message, $code, $previous);
    }

    public function getUserInformation()
    {
        return $this->userInformation;
    }

    public function getMessageKey()
    {
        return 'You need to finish registration to login.';
    }
}
