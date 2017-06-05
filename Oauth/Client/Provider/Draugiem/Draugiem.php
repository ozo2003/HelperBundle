<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Draugiem;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\AbstractProvider;

class Draugiem extends AbstractProvider
{
    /**
     * Draugiem.lv API URL
     */
    const API_URL = 'http://api.draugiem.lv/json/';

    /**
     * Draugiem.lv passport login URL
     */
    const LOGIN_URL = 'https://api.draugiem.lv/authorize/';

    /**
     * Timeout in seconds for session_check requests
     */
    const SESSION_CHECK_TIMEOUT = 180;

    /**
     * @param array $options
     * @param array $collaborators
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
    }

    public function getBaseAuthorizationUrl()
    {
        return static::LOGIN_URL;
    }

    public function getBaseAccessTokenUrl(array $params = [])
    {
        return static::API_URL;
    }

    public function getDefaultScopes()
    {
        return [];
    }

    protected function createResourceOwner(array $response, AccessToken $token = null)
    {
        return new DraugiemUser($response);
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $message = $data['error']['description'];
            throw new IdentityProviderException($message, $data['error']['code'], $data);
        }
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
    }
}
