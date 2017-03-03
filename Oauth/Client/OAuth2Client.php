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

    private $provider;

    private $requestStack;

    private $isStateless = false;

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
    
    public function token(array $options = []){
        $url = $this->provider->getBaseAccessTokenUrl();
        
        $url .= '&grant_type=password&username=dzalitis&password=aaaaaaaa';
        
        // return new RedirectResponse($url);
        global $kernel;
        
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        
        $service = $kernel->getContainer()->get('sludio_helper.oauth.base_service');
        $options = array(
            'grant_type' => 'password',
            'username' => 'dzalitis',
            'password' => 'aaaaaaaa',
            'url' => $url
        );
        $options = array_merge($this->provider->getTokenData(), $options);
        
        return $service->sendRequest($options);
        // return 
    }

    public function getAccessToken()
    {
        if (!$this->isStateless) {
            $expectedState = $this->getSession()->get(self::OAUTH2_SESSION_STATE_KEY);
            $actualState = $this->getCurrentRequest()->query->get('state');
            if (!$actualState || ($actualState !== $expectedState)) {
                throw new InvalidStateException('Invalid state: '.var_export(var_export($actualState,1).var_export($expectedState,1),1));
            }
        }

        $code = $this->getCurrentRequest()->get('code');

        if (!$code) {
            throw new MissingAuthorizationCodeException('No "code" parameter was found (usually this is a query parameter)!');
        }

        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    public function fetchUserFromToken(AccessToken $accessToken)
    {
        return $this->provider->getResourceOwner($accessToken);
    }

    public function fetchUser()
    {
        $token = $this->getAccessToken();

        return $this->fetchUserFromToken($token);
    }

    public function getOAuth2Provider()
    {
        return $this->provider;
    }

    private function getCurrentRequest()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new \LogicException('There is no "current request", and it is needed to perform this action');
        }

        return $request;
    }

    private function getSession()
    {
        $session = $this->getCurrentRequest()->getSession();

        if (!$session) {
            throw new \LogicException('In order to use "state", you must have a session. Set the OAuth2Client to stateless to avoid state');
        }

        return $session;
    }
}