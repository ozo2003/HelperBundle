<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Facebook;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Sludio\HelperBundle\Oauth\Client\Provider\BaseProvider;
use Sludio\HelperBundle\Oauth\Exception\FacebookProviderException;

class Facebook extends BaseProvider
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
    public function __construct($options = [], array $collaborators = [], $generator = null)
    {
        parent::__construct($options, $collaborators, $generator);

        if (empty($options['graphApiVersion'])) {
            throw new InvalidArgumentException('error_facebook_graph_api_version_not_set');
        } elseif (!preg_match(self::GRAPH_API_VERSION_REGEX, $options['graphApiVersion'])) {
            throw new InvalidArgumentException('error_facebook_wrong_graph_api_version');
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
        return [
            'public_profile',
            'email',
        ];
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $fields = [
            'id',
            'name',
            'first_name',
            'last_name',
            'email',
            'hometown',
            'picture.type(large){url,is_silhouette}',
            'cover{source}',
            'gender',
            'locale',
            'link',
            'timezone',
            'age_range',
        ];

        // backwards compatibility with less than v2.8
        if ((float)substr($this->graphApiVersion, 1) < 2.8) {
            $fields[] = 'bio';
        }

        $appSecretProof = AppSecretProof::create($this->clientSecret, $token->getToken());

        return $this->getBaseGraphUrl().$this->graphApiVersion.'/me?fields='.implode(',', $fields).'&access_token='.$token.'&appsecret_proof='.$appSecretProof;
    }

    public function getAccessToken($grant = 'authorization_code', array $params = [], array $attributes = [])
    {
        if (isset($params['refresh_token'])) {
            throw new FacebookProviderException('error_facebook_token_refresh_not_supported');
        }

        return parent::getAccessToken($grant, $params, $attributes);
    }

    public function getLongLivedAccessToken($accessToken)
    {
        $params = [
            'fb_exchange_token' => (string)$accessToken,
        ];

        return $this->getAccessToken('fb_exchange_token', $params);
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new FacebookUserInterface($response);
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            throw new IdentityProviderException('error_facebook_bad_response', 400, $response->getBody());
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
