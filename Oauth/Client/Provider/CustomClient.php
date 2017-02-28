<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider;

use Sludio\HelperBundle\Oauth\Client\OAuth2Client;
use League\OAuth2\Client\Token\AccessToken;

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
