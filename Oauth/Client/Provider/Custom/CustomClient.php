<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Custom;

use League\OAuth2\Client\Token\AccessToken;
use Sludio\HelperBundle\Oauth\Client\OAuth2Client;

class CustomClient extends OAuth2Client
{
    public function fetchUserFromToken(AccessToken $accessToken)
    {
        return parent::fetchUserFromToken($accessToken);
    }

    public function fetchUser()
    {
        return parent::fetchUser();
    }
}
