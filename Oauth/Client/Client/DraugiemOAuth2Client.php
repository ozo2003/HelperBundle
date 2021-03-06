<?php

namespace Sludio\HelperBundle\Oauth\Client\Client;

use Sludio\HelperBundle\Oauth\Client\OAuth2Client;
use Sludio\HelperBundle\Oauth\Client\Provider\Draugiem\DraugiemUser;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DraugiemOAuth2Client extends OAuth2Client
{
    const METHOD = 'POST';

    public function redirect(array $scopes = [], array $options = [], $token = null)
    {
        $redirect = $this->provider->getRedirectUri();
        if ($token) {
            $redirect = str_replace('placeholder', $token, $redirect);
        }

        $data = [
            'hash' => md5($this->provider->getClientSecret().$redirect),
            'redirect' => $redirect,
            'app' => $this->provider->getClientId(),
        ];

        $url = $this->provider->getBaseAuthorizationUrl().'?'.http_build_query($data);

        if (!$this->isStateless) {
            $this->getSession()->set(self::OAUTH2_SESSION_STATE_KEY, $this->provider->getState());
        }

        return new RedirectResponse($url);
    }

    public function fetchUser(array $attributes = [])
    {
        $user = $this->returnRedirect();

        return new DraugiemUser($user);
    }

    public function returnRedirect()
    {
        $data = [
            'app' => $this->provider->getClientSecret(),
            'code' => $this->getCurrentRequest()->get('dr_auth_code'),
            'action' => 'authorize',
        ];

        $url = $this->provider->getBaseAccessTokenUrl().'?'.http_build_query($data);

        $factory = $this->provider->getRequestFactory();
        $request = $factory->getRequestWithOptions(static::METHOD, $url, $data);

        return $this->provider->getParsedResponse($request);
    }
}
