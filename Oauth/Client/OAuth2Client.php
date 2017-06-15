<?php

namespace Sludio\HelperBundle\Oauth\Client;

use Sludio\HelperBundle\Oauth\Exception\InvalidStateException;
use Sludio\HelperBundle\Oauth\Exception\MissingAuthorizationCodeException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class OAuth2Client
{
    const OAUTH2_SESSION_STATE_KEY = 'sludio_helper.oauth_client_state';

    protected $provider;

    protected $requestStack;

    protected $isStateless = true;

    public function __construct(AbstractProvider $provider, RequestStack $requestStack)
    {
        $this->provider = $provider;
        $this->requestStack = $requestStack;
    }

    public function setAsStateless()
    {
        $this->isStateless = true;
    }

    public function redirect(array $scopes = [], array $options = [])
    {
        if (!empty($scopes)) {
            $options['scope'] = $scopes;
        }

        $url = $this->provider->getAuthorizationUrl($options);

        if (!$this->isStateless) {
            $this->getSession()->set(
                self::OAUTH2_SESSION_STATE_KEY,
                $this->provider->getState()
            );
        }

        return new RedirectResponse($url);
    }

    public function getAccessToken(array $attributes = [])
    {
        if (!$this->isStateless) {
            $expectedState = $this->getSession()->get(self::OAUTH2_SESSION_STATE_KEY);
            $actualState = $this->getCurrentRequest()->query->get('state');
            if (!$actualState || ($actualState !== $expectedState)) {
                throw new InvalidStateException('Invalid state: '.var_export(var_export($actualState,1).var_export($expectedState,1),1), 401);
            }
        }

        $code = $this->getCurrentRequest()->get('code');

        if (!$code) {
            throw new MissingAuthorizationCodeException('No "code" parameter was found!', 401);
        }

        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ],
        $attributes
        );
    }

    public function fetchUserFromToken(AccessToken $accessToken)
    {
        return $this->provider->getResourceOwner($accessToken);
    }

    public function fetchUser(array $attributes = [])
    {
        $token = $this->getAccessToken($attributes);

        return $this->fetchUserFromToken($token);
    }

    public function getOAuth2Provider()
    {
        return $this->provider;
    }

    protected function getCurrentRequest()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new \LogicException('There is no "current request", and it is needed to perform this action', 400);
        }

        return $request;
    }

    protected function getSession()
    {
        $session = $this->getCurrentRequest()->getSession();

        if (!$session) {
            throw new \LogicException('In order to use "state", you must have a session. Set the OAuth2Client to stateless to avoid state', 400);
        }

        return $session;
    }
}
