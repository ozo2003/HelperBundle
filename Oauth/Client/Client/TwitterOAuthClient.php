<?php

namespace Sludio\HelperBundle\Oauth\Client\Client;

use Abraham\TwitterOAuth\TwitterOAuth;
use Exception;
use Sludio\HelperBundle\Logger\SludioLogger;
use Sludio\HelperBundle\Oauth\Client\OAuth2Client;
use Sludio\HelperBundle\Oauth\Client\Provider\Twitter\TwitterUser;
use Sludio\HelperBundle\Oauth\Exception\InvalidStateException;
use Sludio\HelperBundle\Oauth\Exception\MissingAuthorizationCodeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class TwitterOAuthClient extends OAuth2Client
{
    protected $session;

    const URL_REQUEST_TOKEN = 'oauth/request_token';
    const URL_AUTHORIZE = 'oauth/authorize';
    const URL_ACCESS_TOKEN = 'oauth/access_token';

    public function __construct($provider, RequestStack $requestStack, SludioLogger $logger)
    {
        parent::__construct($provider, $requestStack, $logger);
        $this->session = $this->requestStack->getCurrentRequest()->getSession();
    }

    public function getRequestToken()
    {
        $request_token = $this->provider->twitter->oauth(static::URL_REQUEST_TOKEN, ['oauth_callback' => $this->provider->getRedirectUri()]);

        if ($this->provider->twitter->getLastHttpCode() !== 200) {
            $this->logger->error(__CLASS__.' ('.__LINE__.'): '.'There was a problem performing this request', $this->provider->twitter->getLastHttpCode());
            throw new Exception('error_twitter_bad_response');
        }

        $this->session->set('oauth_token', $request_token['oauth_token']);
        $this->session->set('oauth_token_secret', $request_token['oauth_token_secret']);

        return $request_token['oauth_token'];
    }

    public function redirect(array $scopes = [], array $options = [], $token = null)
    {
        $url = $this->provider->twitter->url(static::URL_AUTHORIZE, ['oauth_token' => $this->getRequestToken()]);

        if (!$this->isStateless) {
            $this->getSession()->set(self::OAUTH2_SESSION_STATE_KEY, $this->provider->getState());
        }

        return new RedirectResponse($url);
    }

    public function getAccessToken(array $attributes = [])
    {
        if (!$this->isStateless) {
            $expectedState = $this->getSession()->get(self::OAUTH2_SESSION_STATE_KEY);
            $actualState = $this->getCurrentRequest()->query->get('state');
            if (!$actualState || ($actualState !== $expectedState)) {
                $this->logger->error(__CLASS__.' ('.__LINE__.'): '.'Invalid state: '.serialize($actualState).', '.serialize($expectedState), 401);
                throw new InvalidStateException('error_oauth_invalid_state');
            }
        }

        $code = $this->getCurrentRequest()->get('oauth_verifier');
        $token = $this->getCurrentRequest()->get('oauth_token');

        if (!$code) {
            $this->logger->error(__CLASS__.' ('.__LINE__.'): '.'No "oauth_verifier" parameter was found!', 401);
            throw new MissingAuthorizationCodeException('error_twitter_oauth_verifier_parameter_not_found');
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
        } catch (Exception $e) {
            if ($this->provider->twitter->getLastHttpCode() !== 200) {
                $this->logger->error(__CLASS__.' ('.__LINE__.'): '.$e->getMessage(), $this->provider->twitter->getLastHttpCode());
                throw new Exception('error_twitter_bad_response');
            }
        }

        return new TwitterUser($user_token);
    }
}
