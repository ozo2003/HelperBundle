<?php

namespace Sludio\HelperBundle\Oauth\Security\Authenticator;

use Sludio\HelperBundle\Oauth\Security\Exception\NoAuthCodeAuthenticationException;
use Sludio\HelperBundle\Oauth\Exception\MissingAuthorizationCodeException;
use Sludio\HelperBundle\Oauth\Security\Helper\FinishRegistrationBehavior;
use Sludio\HelperBundle\Oauth\Security\Helper\PreviousUrlHelper;
use Sludio\HelperBundle\Oauth\Security\Helper\SaveAuthFailureMessage;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Sludio\HelperBundle\Oauth\Client\OAuth2Client;

abstract class SocialAuthenticator extends AbstractGuardAuthenticator
{
    use FinishRegistrationBehavior;
    use PreviousUrlHelper;
    use SaveAuthFailureMessage;

    protected function fetchAccessToken(OAuth2Client $client)
    {
        try {
            return $client->getAccessToken();
        } catch (MissingAuthorizationCodeException $e) {
            throw new NoAuthCodeAuthenticationException();
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function supportsRememberMe()
    {
        return true;
    }
}
