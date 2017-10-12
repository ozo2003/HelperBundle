<?php

namespace Sludio\HelperBundle\Openidconnect\Provider;

use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken as BaseAccessToken;
use Psr\Http\Message\ResponseInterface;
use Sludio\HelperBundle\Openidconnect\Component\Providerable;
use Sludio\HelperBundle\Openidconnect\Security\Exception\InvalidTokenException;
use Sludio\HelperBundle\Openidconnect\Validator;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OpenIDConnectProvider extends AbstractProvider implements Providerable
{
    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var Signer
     */
    protected $signer;

    /**
     * @var ValidatorChain
     */
    protected $validatorChain;

    /**
     * @var string
     */
    protected $idTokenIssuer;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Uri[]
     */
    protected $uris = [];

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @param string $key
     * @param array  $options
     * @param array  $collaborators
     * @param Router $router
     */
    public function __construct(string $key, array $options = [], array $collaborators = [], Router $router)
    {
        $this->signer = new Sha256();

        $this->validatorChain = new Validator\ValidatorChain();
        $this->validatorChain->setValidators([
            new Validator\NotEmpty('iat', true),
            new Validator\GreaterOrEqualsTo('exp', true),
            new Validator\EqualsTo('iss', true),
            new Validator\EqualsTo('aud', true),
            new Validator\NotEmpty('sub', true),
            new Validator\LesserOrEqualsTo('nbf'),
            new Validator\EqualsTo('jti'),
            new Validator\EqualsTo('azp'),
            new Validator\EqualsTo('nonce'),
        ]);

        $this->router = $router;

        parent::__construct($options, $collaborators);
        $this->buildParams($options);
    }

    private function buildParams(array $options = [])
    {
        if (!empty($options)) {
            $this->clientId = $options['client_key'];
            $this->clientSecret = $options['client_secret'];
            $this->idTokenIssuer = $options['id_token_issuer'];
            $this->publicKey = 'file://'.$options['public_key'];
            $this->state = $this->getRandomState();
            $this->baseUri = $options['base_uri'];
            switch ($options['redirect']['type']) {
                case 'uri':
                    $url = $options['redirect']['uri'];
                    break;
                case 'route':
                    $params = !empty($options['redirect']['params']) ? $options['redirect']['params'] : [];
                    $url = $this->router->generate($options['redirect']['route'], $params, UrlGeneratorInterface::ABSOLUTE_URL);
                    break;
            }
            $this->redirectUri = $url;

            foreach ($options['uris'] as $name => $uri) {
                $opt = [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $this->redirectUri,
                    'state' => $this->state,
                    'base_uri' => $this->baseUri,
                ];
                $this->uris[$name] = new Uri($uri, $opt);
            }
        }
    }

    /**
     * Returns all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions()
    {
        return [];
    }

    public function getPublicKey()
    {
        return new Key($this->publicKey);
    }

    /**
     * Requests an access token using a specified grant and option set.
     *
     * @param  mixed $grant
     * @param  array $options
     *
     * @return AccessToken
     */
    public function getAccessToken($grant, array $options = [])
    {
        /** @var Token $token */
        $accessToken = parent::getAccessToken($grant, $options);

        if (null === $accessToken) {
            throw new InvalidTokenException('Invalid access token.');
        }

        $token = $accessToken->getIdToken();

        // id_token is empty.
        if (null === $token) {
            throw new InvalidTokenException('Expected an id_token but did not receive one from the authorization server.');
        }

        // If the ID Token is received via direct communication between the Client and the Token Endpoint
        // (which it is in this flow), the TLS server validation MAY be used to validate the issuer in place of checking
        // the token signature. The Client MUST validate the signature of all other ID Tokens according to JWS [JWS]
        // using the algorithm specified in the JWT alg Header Parameter. The Client MUST use the keys provided by
        // the Issuer.
        //
        // The alg value SHOULD be the default of RS256 or the algorithm sent by the Client in the
        // id_token_signed_response_alg parameter during Registration.
        if (false === $token->verify($this->signer, $this->getPublicKey())) {
            throw new InvalidTokenException('Received an invalid id_token from authorization server.');
        }

        // validations
        // @see http://openid.net/specs/openid-connect-core-1_0.html#IDTokenValidation
        // validate the iss (issuer)
        // - The Issuer Identifier for the OpenID Provider (which is typically obtained during Discovery)
        // MUST exactly match the value of the iss (issuer) Claim.
        // validate the aud
        // - The Client MUST validate that the aud (audience) Claim contains its client_id value registered at the Issuer
        // identified by the iss (issuer) Claim as an audience. The aud (audience) Claim MAY contain an array with more
        // than one element. The ID Token MUST be rejected if the ID Token does not list the Client as a valid audience,
        // or if it contains additional audiences not trusted by the Client.
        // - If a nonce value was sent in the Authentication Request, a nonce Claim MUST be present and its value checked
        // to verify that it is the same value as the one that was sent in the Authentication Request. The Client SHOULD
        // check the nonce value for replay attacks. The precise method for detecting replay attacks is Client specific.
        // - If the auth_time Claim was requested, either through a specific request for this Claim or by using
        // the max_age parameter, the Client SHOULD check the auth_time Claim value and request re-authentication if it
        // determines too much time has elapsed since the last End-User authentication.
        // TODO
        // If the acr Claim was requested, the Client SHOULD check that the asserted Claim Value is appropriate.
        // The meaning and processing of acr Claim Values is out of scope for this specification.
        $currentTime = time();
        $data = [
            'iss' => $this->getIdTokenIssuer(),
            'exp' => $currentTime,
            'auth_time' => $currentTime,
            'iat' => $currentTime,
            'nbf' => $currentTime,
            'aud' => $this->clientId,
        ];

        // If the ID Token contains multiple audiences, the Client SHOULD verify that an azp Claim is present.
        // If an azp (authorized party) Claim is present, the Client SHOULD verify that its client_id is the Claim Value.
        if ($token->hasClaim('azp')) {
            $data['azp'] = $this->clientId;
        }

        if (false === $this->validatorChain->validate($data, $token)) {
            ld($this->validatorChain->getMessages());
            throw new InvalidTokenException('The id_token did not pass validation.');
        }

        return $accessToken;
    }

    /**
     * Overload parent as OpenID Connect specification states scopes shall be separated by spaces
     *
     * @return string
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * @return ValidatorChain|void
     */
    public function getValidatorChain()
    {
        return $this->validatorChain;
    }

    /**
     * Get the issuer of the OpenID Connect id_token
     *
     * @return string
     */
    protected function getIdTokenIssuer()
    {
        return $this->idTokenIssuer;
    }

    public function check(array $response = [])
    {
        return true;
    }

    /**
     * Creates an access token from a response.
     *
     * The grant that was used to fetch the response can be used to provide
     * additional context.
     *
     * @param  array         $response
     * @param  AbstractGrant $grant
     *
     * @return AccessToken
     */
    protected function createAccessToken(array $response, AbstractGrant $grant)
    {
        if ($this->check($response)) {
            return new AccessToken($response);
        }

        return null;
    }

    public function getBaseAuthorizationUrl()
    {
        return '';
    }

    public function getBaseAccessTokenUrl(array $params = [])
    {
        return '';
    }

    public function getDefaultScopes()
    {
        return [];
    }

    protected function createResourceOwner(array $response, BaseAccessToken $token = null)
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
    }

    public function getResourceOwnerDetailsUrl(BaseAccessToken $token)
    {
    }

    public function getUri($name)
    {
        return $this->uris[$name];
    }
}
