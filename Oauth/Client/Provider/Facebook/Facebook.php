<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Facebook;

use League\OAuth2\Client\Token\AccessToken;
use Sludio\HelperBundle\Oauth\Exception\FacebookProviderException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\AbstractProvider;

class Facebook extends AbstractProvider
{
    /**
     * Production Graph API URL.
     *
     * @const string
     */
    const BASE_FACEBOOK_URL = 'https://www.facebook.com/';

    /**
     * Production Graph API URL.
     *
     * @const string
     */
    const BASE_GRAPH_URL = 'https://graph.facebook.com/';

    /**
     * Regular expression used to check for graph API version format
     *
     * @const string
     */
    const GRAPH_API_VERSION_REGEX = '~^v\d+\.\d+$~';

    /**
     * The Graph API version to use for requests.
     *
     * @var string
     */
    protected $graphApiVersion;

    /**
     * @param array $options
     * @param array $collaborators
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (empty($options['graphApiVersion'])) {
            $message = 'The "graphApiVersion" option not set. Please set a default Graph API version.';
            throw new \InvalidArgumentException($message);
        } elseif (!preg_match(self::GRAPH_API_VERSION_REGEX, $options['graphApiVersion'])) {
            $message = 'The "graphApiVersion" must start with letter "v" followed by version number, ie: "v2.4".';
            throw new \InvalidArgumentException($message);
        }

        $this->graphApiVersion = $options['graphApiVersion'];
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->getBaseFacebookUrl().$this->graphApiVersion.'/dialog/oauth';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getBaseGraphUrl().$this->graphApiVersion.'/oauth/access_token';
    }

    public function getDefaultScopes()
    {
        return ['public_profile', 'email'];
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $fields = [
            'id', 'name', 'first_name', 'last_name',
            'email', 'hometown', 'picture.type(large){url,is_silhouette}',
            'cover{source}', 'gender', 'locale', 'link', 'timezone', 'age_range'
        ];

        // backwards compatibility less than 2.8
        if ((float) substr($this->graphApiVersion, 1) < 2.8) {
            $fields[] = 'bio';
        }

        $appSecretProof = AppSecretProof::create($this->clientSecret, $token->getToken());

        return $this->getBaseGraphUrl().$this->graphApiVersion.'/me?fields='.implode(',', $fields).'&access_token='.$token.'&appsecret_proof='.$appSecretProof;
    }

    public function getAccessToken($grant = 'authorization_code', array $params = [])
    {
        if (isset($params['refresh_token'])) {
            throw new FacebookProviderException('Facebook does not support token refreshing.');
        }

        return parent::getAccessToken($grant, $params);
    }

    public function getLongLivedAccessToken($accessToken)
    {
        $params = [
            'fb_exchange_token' => (string) $accessToken,
        ];

        return $this->getAccessToken('fb_exchange_token', $params);
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new FacebookUser($response);
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $message = $data['error']['message'];
            throw new IdentityProviderException($message, $data['error']['code'], $data);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getContentType(ResponseInterface $response)
    {
        $type = parent::getContentType($response);

        // Fix for Facebook's pseudo-JSONP support
        if (strpos($type, 'javascript') !== false) {
            return 'application/json';
        }

        // Fix for Facebook's pseudo-urlencoded support
        if (strpos($type, 'plain') !== false) {
            return 'application/x-www-form-urlencoded';
        }

        return $type;
    }

    /**
     * Get the base Facebook URL.
     *
     * @return string
     */
    private function getBaseFacebookUrl()
    {
        return static::BASE_FACEBOOK_URL;
    }

    /**
     * Get the base Graph API URL.
     *
     * @return string
     */
    private function getBaseGraphUrl()
    {
        return static::BASE_GRAPH_URL;
    }
}