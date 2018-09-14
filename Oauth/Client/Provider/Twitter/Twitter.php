<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use GuzzleHttp\Client as HttpClient;
use League\OAuth2\Client\Grant\GrantFactory;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\RequestFactory;
use Psr\Http\Message\ResponseInterface;

class Twitter extends AbstractProvider
{
    const URL_REQUEST_TOKEN = 'oauth/request_token';
    const URL_AUTHORIZE = 'oauth/authorize';
    const URL_ACCESS_TOKEN = 'oauth/access_token';
    public $twitter;

    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * @param array $options       An array of options to set on this provider.
     *                             Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *                             Individual providers may introduce more options, as needed.
     * @param array $collaborators An array of collaborators that may be used to
     *                             override this provider's default behavior. Collaborators include
     *                             `grantFactory`, `requestFactory`, and `httpClient`.
     *                             Individual providers may introduce more collaborators, as needed.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->{$option} = $value;
            }
        }

        if (empty($collaborators['grantFactory'])) {
            $collaborators['grantFactory'] = new GrantFactory();
        }
        $this->setGrantFactory($collaborators['grantFactory']);

        if (empty($collaborators['requestFactory'])) {
            $collaborators['requestFactory'] = new RequestFactory();
        }
        $this->setRequestFactory($collaborators['requestFactory']);

        if (empty($collaborators['httpClient'])) {
            $client_options = $this->getAllowedClientOptions($options);

            $collaborators['httpClient'] = new HttpClient(array_intersect_key($options, array_flip($client_options)));
        }
        $this->setHttpClient($collaborators['httpClient']);
        $this->twitter = new TwitterOAuth($this->getClientId(), $this->getClientSecret());
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function getBaseAuthorizationUrl()
    {
    }

    public function getBaseAccessTokenUrl(array $params = null)
    {
    }

    public function getDefaultScopes()
    {
        return [];
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    protected function createResourceOwner(array $response, AccessToken $token = null)
    {
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
    }

    public function setState($state = null)
    {
        $this->state = $state;
    }
}
