<?php

namespace Sludio\HelperBundle\Oauth\Client\Client;

use Sludio\HelperBundle\Oauth\Client\OAuth2Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sludio\HelperBundle\Oauth\Exception\InvalidStateException;
use Sludio\HelperBundle\Oauth\Exception\MissingAuthorizationCodeException;
use Sludio\HelperBundle\Oauth\Client\Provider\Draugiem\DraugiemUser;

class DraugiemOAuth2Client extends OAuth2Client
{
    protected $isStateless = true;

    public function redirect(array $scopes = [], array $options = [])
    {
        if (!empty($scopes)) {
            $options['scope'] = $scopes;
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

    public function getAccessToken($code = null)
    {
        if (!$this->isStateless) {
            $expectedState = $this->getSession()->get(self::OAUTH2_SESSION_STATE_KEY);
            $actualState = $this->getCurrentRequest()->query->get('state');
            if (!$actualState || ($actualState !== $expectedState)) {
                throw new InvalidStateException('Invalid state: '.var_export(var_export($actualState, 1).var_export($expectedState, 1), 1), 401);
            }
        }

        if (!$code) {
            throw new MissingAuthorizationCodeException('No "dr_auth_code" parameter was found!', 401);
        }

        return $this->provider->getAccessToken('authorization_code', [
            'apikey' => $code,
            'code' => $code,
        ]);
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
        $request = $factory->getRequestWithOptions('POST', $url, $data);

        return $this->provider->getParsedResponse($request);
    }
}
