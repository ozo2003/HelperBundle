<?php

namespace Sludio\HelperBundle\Openidconnect\Provider;

use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Http\Message\RequestInterface;
use Sludio\HelperBundle\Openidconnect\Component\Providerable;
use Sludio\HelperBundle\Openidconnect\Security\Exception\InvalidTokenException;
use Sludio\HelperBundle\Openidconnect\Specification;
use Sludio\HelperBundle\Script\Security\Exception\ErrorException;
use Sludio\HelperBundle\Script\Utils\Helper;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class OpenIDConnectProvider extends AbstractProvider implements Providerable
{
    use VariableTrait;

    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    /**
     * @param array   $options
     * @param array   $collaborators
     * @param Router  $router
     * @param Session $session
     */
    public function __construct(array $options = [], array $collaborators = [], Router $router, Session $session)
    {
        $this->signer = new Sha256();

        $this->validatorChain = new Specification\ValidatorChain();
        $this->validatorChain->setValidators([
            new Specification\NotEmpty('iat', true),
            new Specification\GreaterOrEqualsTo('exp', true),
            new Specification\EqualsTo('iss', true),
            new Specification\EqualsTo('aud', true),
            new Specification\NotEmpty('sub', true),
            new Specification\LesserOrEqualsTo('nbf'),
            new Specification\EqualsTo('jti'),
            new Specification\EqualsTo('azp'),
            new Specification\EqualsTo('nonce'),
        ]);

        $this->router = $router;
        $this->session = $session;

        parent::__construct($options, $collaborators);
        $this->buildParams($options);
    }

    private function buildParams(array $options = [])
    {
        if (!empty($options)) {
            $this->clientId = $options['client_key'];
            $this->clientSecret = $options['client_secret'];
            unset($options['client_secret'], $options['client_key']);
            $this->idTokenIssuer = $options['id_token_issuer'];
            $this->publicKey = 'file://'.$options['public_key'];
            $this->state = $this->getRandomState();
            $this->baseUri = $options['base_uri'];
            $this->useSession = $options['use_session'];
            $url = null;
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

            $this->buildUris($options);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getRandomState($length = 32)
    {
        return Helper::getUniqueId($length);
    }

    private function buildUris($options = [])
    {
        foreach ($options['uris'] as $name => $uri) {
            $opt = [
                'client_id' => $this->clientId,
                'redirect_uri' => $this->redirectUri,
                'state' => $this->state,
                'base_uri' => $this->baseUri,
            ];
            $method = isset($uri['method']) ? $uri['method'] : self::METHOD_POST;
            $this->uris[$name] = new Uri($uri, $opt, $this->useSession, $method, $this->session);
        }
    }

    /**
     * Requests an access token using a specified grant and option set.
     *
     * @param  mixed $grant
     * @param  array $options
     *
     * @return AccessToken
     * @throws InvalidTokenException
     * @throws \BadMethodCallException
     * @throws ErrorException
     */
    public function getAccessToken($grant, array $options = [])
    {
        /** @var AccessToken $token */
        $accessToken = $this->getAccessTokenFunction($grant, $options);

        if (null === $accessToken) {
            throw new InvalidTokenException('Invalid access token.');
        }

        $token = $accessToken->getIdToken();
        // id_token is empty.
        if (null === $token) {
            throw new InvalidTokenException('Expected an id_token but did not receive one from the authorization server.');
        }

        if (false === $token->verify($this->signer, $this->getPublicKey())) {
            throw new InvalidTokenException('Received an invalid id_token from authorization server.');
        }

        $currentTime = time();
        $data = [
            'iss' => $this->getIdTokenIssuer(),
            'exp' => $currentTime,
            'auth_time' => $currentTime,
            'iat' => $currentTime,
            'nbf' => $currentTime,
            'aud' => $this->clientId,
        ];

        if ($token->hasClaim('azp')) {
            $data['azp'] = $this->clientId;
        }

        if (false === $this->validatorChain->validate($data, $token)) {
            throw new InvalidTokenException('The id_token did not pass validation.');
        }

        if ($this->useSession) {
            $this->session->set('access_token', $accessToken->getToken());
            $this->session->set('refresh_token', $accessToken->getRefreshToken());
            $this->session->set('id_token', $accessToken->getIdTokenHint());
        }

        return $accessToken;
    }

    /**
     * @inheritdoc
     *
     * @throws ErrorException
     */
    public function getAccessTokenFunction($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'redirect_uri' => $this->redirectUri,
        ];

        $params = $grant->prepareRequestParameters($params, $options);
        $request = $this->getAccessTokenRequest($params);
        $response = $this->getResponse($request);
        if (!\is_array($response)) {
            throw new ErrorException('Invalid request parameters');
        }
        $prepared = $this->prepareAccessTokenResponse($response);

        return $this->createAccessToken($prepared, $grant);
    }

    public function getResponse(RequestInterface $request)
    {
        $response = $this->sendRequest($request);
        $this->statusCode = $response->getStatusCode();
        $parsed = $this->parseResponse($response);
        $this->checkResponse($response, $parsed);

        return $parsed;
    }

    /**
     * Creates an access token from a response.
     *
     * The grant that was used to fetch the response can be used to provide
     * additional context.
     *
     * @param  array             $response
     * @param AbstractGrant|null $grant
     *
     * @return AccessToken
     */
    protected function createAccessToken(array $response, AbstractGrant $grant = null)
    {
        if ($this->check($response)) {
            return new AccessToken($response);
        }

        return null;
    }

    public function getPublicKey()
    {
        return new Key($this->publicKey);
    }

    public function getRefreshToken($token, array $options = [])
    {
        $params = [
            'token' => $token,
            'grant_type' => 'refresh_token',
        ];
        $params = array_merge($params, $options);
        $request = $this->getRefreshTokenRequest($params);

        return $this->getResponse($request);
    }

    protected function getRefreshTokenRequest(array $params)
    {
        $method = $this->getAccessTokenMethod();
        $url = $this->getRefreshTokenUrl();
        $options = $this->getAccessTokenOptions($params);

        return $this->getRequest($method, $url, $options);
    }

    /**
     * Builds request options used for requesting an access token.
     *
     * @param  array $params
     *
     * @return array
     */
    protected function getAccessTokenOptions(array $params)
    {
        $options = $this->getBaseTokenOptions($params);
        $options['headers']['authorization'] = 'Basic: '.base64_encode($this->clientId.':'.$this->clientSecret);

        return $options;
    }

    protected function getBaseTokenOptions(array $params)
    {
        $options = [
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded',
            ],
        ];
        if ($this->getAccessTokenMethod() === self::METHOD_POST) {
            $options['body'] = $this->getAccessTokenBody($params);
        }

        return $options;
    }

    public function getValidateToken($token, array $options = [])
    {
        $params = [
            'token' => $token,
        ];
        $params = array_merge($params, $options);
        $request = $this->getValidateTokenRequest($params);

        return $this->getResponse($request);
    }

    protected function getValidateTokenRequest(array $params)
    {
        $method = $this->getAccessTokenMethod();
        $url = $this->getValidateTokenUrl();
        $options = $this->getBaseTokenOptions($params);

        return $this->getRequest($method, $url, $options);
    }

    public function getRevokeToken($token, array $options = [])
    {
        $params = [
            'token' => $token,
        ];
        $params = array_merge($params, $options);
        $request = $this->getRevokeTokenRequest($params);

        return $this->getResponse($request);
    }

    protected function getRevokeTokenRequest(array $params)
    {
        $method = $this->getAccessTokenMethod();
        $url = $this->getRevokeTokenUrl();
        $options = $this->getAccessTokenOptions($params);

        return $this->getRequest($method, $url, $options);
    }
}
