<?php

namespace Sludio\HelperBundle\Openidconnect\Provider;

use Sludio\HelperBundle\Openidconnect\Specification;
use Symfony\Component\HttpFoundation\Session\Session;
use Lcobucci\JWT\Signer;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Token\AccessToken as BaseAccessToken;
use League\OAuth2\Client\Provider\AbstractProvider;

abstract class AbstractVariables extends AbstractProvider
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
     * @var Specification\ValidatorChain
     */
    protected $validatorChain;

    /**
     * @var string
     */
    protected $idTokenIssuer;
    /**
     * @var Uri[]
     */
    protected $uris = [];

    /**
     * @var bool
     */
    protected $useSession;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var string
     */
    protected $baseUri;

    protected function checkResponse(ResponseInterface $response, $data)
    {
    }

    public function check($response = null)
    {
        return true || $response;
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

    public function getBaseAuthorizationUrl()
    {
        return '';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return '';
    }

    public function getDefaultScopes()
    {
        return [];
    }

    public function getResourceOwnerDetailsUrl(BaseAccessToken $token)
    {
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

    protected function createResourceOwner(array $response, BaseAccessToken $token)
    {
        return [];
    }

    /**
     * @return Specification\ValidatorChain
     */
    public function getValidatorChain()
    {
        return $this->validatorChain;
    }

    public function getUri($name)
    {
        return $this->uris[$name];
    }
    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
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

    abstract public function getValidateTokenUrl();

    abstract public function getRefreshTokenUrl();

    abstract public function getRevokeTokenUrl();
}
