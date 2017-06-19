<?php

namespace Sludio\HelperBundle\Oauth\Client\Client;

use Sludio\HelperBundle\Oauth\Client\OAuth2Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sludio\HelperBundle\Oauth\Exception\InvalidStateException;
use Sludio\HelperBundle\Oauth\Exception\MissingAuthorizationCodeException;
use Sludio\HelperBundle\Oauth\Client\Provider\Draugiem\DraugiemUser;

class DraugiemOAuth2Client extends OAuth2Client
{
    const METHOD = 'POST';

    protected $isStateless = true;

    public function redirect(array $scopes = [], array $options = [], $token = null)
    {
        if (!empty($scopes)) {
            $options['scope'] = $scopes;
        }

        if($token){
            $options['token'] = $token;
        }

        $data = [
            'hash' => md5($this->provider->getClientSecret().$this->provider->getRedirectUri()),
            'redirect' => $this->provider->getRedirectUri(),
            'app' => $this->provider->getClientId()
        ];

        $url = $this->provider->getBaseAuthorizationUrl().'?'.http_build_query($data);

        if (!$this->isStateless) {
            $this->getSession()->set(
                self::OAUTH2_SESSION_STATE_KEY,
                $this->provider->getState()
            );
        }

        return new RedirectResponse($url);
    }

    public function fetchUser()
    {
        $user = $this->returnRedirect();

        return new DraugiemUser($user);
    }

    public function returnRedirect(array $scopes = [], array $options = [])
    {
        if (!empty($scopes)) {
            $options['scope'] = $scopes;
        }

        $data = [
            'app' => $this->provider->getClientSecret(),
            'code' => $this->getCurrentRequest()->get('dr_auth_code'),
            'action' => 'authorize'
        ];

        $url = $this->provider->getBaseAccessTokenUrl().'?'.http_build_query($data);

        $factory = $this->provider->getRequestFactory();
        $request = $factory->getRequestWithOptions(static::METHOD, $url, $data);

        return $this->provider->getParsedResponse($request);
    }
}
