<?php

namespace Sludio\HelperBundle\Oauth\Client\Client;

use Abraham\TwitterOAuth\TwitterOAuth;
use Sludio\HelperBundle\Oauth\Client\OAuth2Client;
use Sludio\HelperBundle\Oauth\Client\Provider\Twitter\TwitterUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Sludio\HelperBundle\Script\Security\Exception\ErrorException;

class TwitterOAuthClient extends OAuth2Client
{
    const URL_REQUEST_TOKEN = 'oauth/request_token';
    const URL_AUTHORIZE = 'oauth/authorize';
    const URL_ACCESS_TOKEN = 'oauth/access_token';
    protected $session;

    public function __construct($provider, RequestStack $requestStack)
    {
        parent::__construct($provider, $requestStack);
        $this->session = $this->requestStack->getCurrentRequest()->getSession();
        $this->setAsStateless();
    }

    public function redirect(array $scopes = [], array $options = [], $state = null)
    {
        if (!$this->isStateless) {
            $this->getSession()->set(self::OAUTH2_SESSION_STATE_KEY, $state ?: $this->provider->getState());
            if ($state) {
                $this->provider->setState($state);
            }
        }

        return new RedirectResponse($this->provider->twitter->url(static::URL_AUTHORIZE, ['oauth_token' => $this->getRequestToken()]));
    }

    public function getRequestToken()
    {
        $request_token = $this->provider->twitter->oauth(static::URL_REQUEST_TOKEN, ['oauth_callback' => $this->provider->getRedirectUri()]);

        if ($this->provider->twitter->getLastHttpCode() !== RedirectResponse::HTTP_OK) {
            throw new ErrorException('There was a problem performing this request');
        }

        $this->session->set('oauth_token', $request_token['oauth_token']);
        $this->session->set('oauth_token_secret', $request_token['oauth_token_secret']);

        return $request_token['oauth_token'];
    }

    public function getAccessToken(array $attributes = [])
    {
        if (!$this->isStateless) {
            $expectedState = $this->getSession()->get(self::OAUTH2_SESSION_STATE_KEY);
            $actualState = $this->getCurrentRequest()->query->get('state');
            if (!$actualState || ($actualState !== $expectedState)) {
                throw new ErrorException('Invalid state: '.serialize($actualState).', '.serialize($expectedState));
            }
        }

        $code = $this->getCurrentRequest()->get('oauth_verifier');
        $token = $this->getCurrentRequest()->get('oauth_token');

        if (!$code) {
            throw new ErrorException('No "oauth_verifier" parameter was found');
        }

        return $this->provider->getAccessToken('authorization_code', [
            'verifier' => $code,
            'token' => $token,
            'code' => $code,
        ]);
    }

    public function fetchUser(array $attributes = [])
    {
        $code = $this->getCurrentRequest()->get('oauth_verifier');
        $this->provider->twitter = new TwitterOAuth($this->provider->getClientId(), $this->provider->getClientSecret(), $this->session->get('oauth_token'), $this->session->get('oauth_token_secret'));

        try {
            $user_token = $this->provider->twitter->oauth(static::URL_ACCESS_TOKEN, ['oauth_verifier' => $code]);
        } catch (\Exception $e) {
            if ($this->provider->twitter->getLastHttpCode() !== RedirectResponse::HTTP_OK) {
                throw new ErrorException($e->getMessage());
            }
        }

        return new TwitterUser($user_token);
    }
}
