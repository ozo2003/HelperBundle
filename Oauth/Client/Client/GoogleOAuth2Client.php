<?php

namespace Sludio\HelperBundle\Oauth\Client\Client;

use Sludio\HelperBundle\Oauth\Client\OAuth2Client;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleOAuth2Client extends OAuth2Client
{
    protected $isStateless = false;

    public function redirect(array $scopes = [], array $options = [], $state = null)
    {
        if (!empty($scopes)) {
            $options['scope'] = $scopes;
        }

        if (!$this->isStateless) {
            $this->getSession()->set(
                self::OAUTH2_SESSION_STATE_KEY,
                $state ?: $this->provider->getState()
            );
            if($state){
                $this->provider->setState($state);
            }
        }

        $url = $this->provider->getAuthorizationUrl($options);

        return new RedirectResponse($url);
    }
}
