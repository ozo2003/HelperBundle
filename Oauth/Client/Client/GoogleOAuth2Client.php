<?php

namespace Sludio\HelperBundle\Oauth\Client\Client;

use Sludio\HelperBundle\Logger\SludioLogger;
use Sludio\HelperBundle\Oauth\Client\OAuth2Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class GoogleOAuth2Client extends OAuth2Client
{
    public function __construct($provider, RequestStack $requestStack, SludioLogger $logger)
    {
        parent::__construct($provider, $requestStack, $logger);
        $this->isStateless = false;
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
