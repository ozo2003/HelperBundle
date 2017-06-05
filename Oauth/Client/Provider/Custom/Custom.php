<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Custom;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Custom extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public $domain;
    public $api;
    public $authorize;
    public $token;

    public function __construct(array $options, array $collaborators = [])
    {
        if (isset($options['domain'])) {
            $this->domain = $options['domain'];
        }
        if (isset($options['api'])) {
            $this->api = $options['api'];
        }
        if (isset($options['authorize'])) {
            $this->authorize = $options['authorize'];
        }
        if (isset($options['token'])) {
            $this->token = $options['token'];
        }

        $this->options = $options;
        parent::__construct($options, $collaborators);
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->domain . $this->authorize;
    }

    public function getBaseAccessTokenUrl(array $params = [])
    {
        return $this->domain . $this->token . '?client_id='.$this->options['client_id'] . '&client_secret='.$this->options['client_secret'];
    }

    public function getTokenData(){
        $data = array(
            'url' => $this->domain . $this->token,
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret']
        );

        return $data;
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->domain . $this->api;
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw CustomIdentityProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw CustomIdentityProviderException::oauthException($response, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new CustomResourceOwner($response, $token);

        return $user->setDomain($this->domain);
    }
}
