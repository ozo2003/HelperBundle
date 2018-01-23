<?php

namespace Sludio\HelperBundle\Oauth\Client;

use League\OAuth2\Client\Token\AccessToken;
use Sludio\HelperBundle\Script\Security\Exception\ErrorException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class OAuth2Client
{
    const OAUTH2_SESSION_STATE_KEY = 'sludio_helper.oauth_client_state';

    protected $provider;
    protected $requestStack;
    protected $isStateless = true;

    public function __construct($provider, RequestStack $requestStack)
    {
        $this->provider = $provider;
        $this->requestStack = $requestStack;
    }

    public function setAsStateless()
    {
        $this->isStateless = true;
    }

    public function redirect(array $scopes = [], array $options = [], $token = null)
    {
        if (!empty($scopes)) {
            $options['scope'] = $scopes;
        }

        if ($token) {
            $options['token'] = $token;
        }

        $url = $this->provider->getAuthorizationUrl($options);

        if (!$this->isStateless) {
            $this->getSession()->set(self::OAUTH2_SESSION_STATE_KEY, $this->provider->getState());
        }

        return new RedirectResponse($url);
    }

    protected function getSession()
    {
        $session = $this->getCurrentRequest()->getSession();

        if (!$session) {
            throw new ErrorException('In order to use "state", you must have a session. Set the OAuth2Client to stateless to avoid state');
        }

        return $session;
    }

    protected function getCurrentRequest()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new ErrorException('There is no "current request", and it is needed to perform this action');
        }

        return $request;
    }

    public function fetchUser(array $attributes = [])
    {
        $token = $this->getAccessToken($attributes);

        return $this->fetchUserFromToken($token);
    }

    public function getAccessToken(array $attributes = [])
    {
        if (!$this->isStateless) {
            $expectedState = $this->getSession()->get(self::OAUTH2_SESSION_STATE_KEY);
            $actualState = $this->getCurrentRequest()->query->get('state');
            if (!$actualState || ($actualState !== $expectedState)) {
                throw new ErrorException('Invalid state: '.var_export(var_export($actualState, 1).var_export($expectedState, 1), 1));
            }
        }

        $code = $this->getCurrentRequest()->get('code');

        if (!$code) {
            throw new ErrorException('No "code" parameter was found');
        }

        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ], $attributes);
    }

    public function fetchUserFromToken(AccessToken $accessToken)
    {
        return $this->provider->getResourceOwner($accessToken);
    }

    public function getOAuth2Provider()
    {
        return $this->provider;
    }
}
