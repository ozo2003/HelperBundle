<?php

namespace Sludio\HelperBundle\Oauth\Client\Client;

use Sludio\HelperBundle\Oauth\Client\OAuth2Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class FacebookOAuthClient extends OAuth2Client
{
    public function __construct($provider, RequestStack $requestStack)
    {
        parent::__construct($provider, $requestStack);
        $this->setAsStateless();
    }

    public function redirect(array $scopes = [], array $options = [], $state = null)
    {
        if (!empty($scopes)) {
            $options['scope'] = $scopes;
        }

        if (!$this->isStateless) {
            $this->getSession()->set(self::OAUTH2_SESSION_STATE_KEY, $state ?: $this->provider->getState());
            if ($state) {
                $this->provider->setState($state);
            }
        }

        $options['state'] = $state;
        $url = $this->provider->getAuthorizationUrl($options);

        return new RedirectResponse($url);
    }
}
