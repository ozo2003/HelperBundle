<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Google;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Google extends AbstractProvider
{
    use BearerAuthorizationTrait;

    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * @var string If set, this will be sent to google as the "access_type" parameter.
     * @link https://developers.google.com/accounts/docs/OAuth2WebServer#offline
     */
    protected $accessType;

    /**
     * @var string If set, this will be sent to google as the "hd" parameter.
     * @link https://developers.google.com/accounts/docs/OAuth2Login#hd-param
     */
    protected $hostedDomain;

    /**
     * @var array Default fields to be requested from the user profile.
     * @link https://developers.google.com/+/web/api/rest/latest/people
     */
    protected $defaultUserFields = [
        'id',
        'name(familyName,givenName)',
        'displayName',
        'emails/value',
        'image/url',
    ];
    /**
     * @var array Additional fields to be requested from the user profile.
     *            If set, these values will be included with the defaults.
     */
    protected $userFields = [];

    public function getBaseAuthorizationUrl()
    {
        return 'https://accounts.google.com/o/oauth2/auth';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://accounts.google.com/o/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $fields = array_merge($this->defaultUserFields, $this->userFields);

        $input = [
            'fields' => implode(',', $fields),
            'alt' => 'json',
        ];

        return 'https://www.googleapis.com/plus/v1/people/me?'.http_build_query($input);
    }

    public function setState($state = null)
    {
        $this->state = $state;
    }

    protected function getAuthorizationParameters(array $options)
    {
        $input = [
            'hd' => $this->hostedDomain,
            'access_type' => $this->accessType,
            // if the user is logged in with more than one account ask which one to use for the login!
            'authuser' => '-1',
        ];
        $params = array_merge(parent::getAuthorizationParameters($options), array_filter($input));

        return $params;
    }

    protected function getDefaultScopes()
    {
        return [
            'email',
            'openid',
            'profile',
        ];
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $code = 0;
            $error = $data['error'];

            if (\is_array($error)) {
                $code = $error['code'];
            }

            throw new IdentityProviderException('error_google_bad_response', $code, $response->getBody());
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GoogleUser($response);
    }
}
