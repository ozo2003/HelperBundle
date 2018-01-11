<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseProvider extends AbstractProvider
{
    public $generator;

    public function __construct(array $options = [], array $collaborators = [], UrlGeneratorInterface $generator = null)
    {
        $this->generator = $generator;

        parent::__construct($options, $collaborators);
    }

    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Requests an access token using a specified grant and option set.
     *
     * @param  mixed $grant
     * @param  array $options
     *
     * @param array  $attributes
     *
     * @return AccessToken
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function getAccessToken($grant, array $options = [], array $attributes = [])
    {
        $grant = $this->verifyGrant($grant);

        $redirectUri = null;
        if (!empty($attributes) && $this->generator) {
            $redirectUri = $this->generator->generate($attributes['_route'], $attributes['_route_params'], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $redirectUri ?: $this->redirectUri,
        ];

        $params = $grant->prepareRequestParameters($params, $options);
        $request = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);
        $prepared = $this->prepareAccessTokenResponse($response);

        return $this->createAccessToken($prepared, $grant);
    }

    public function setState($state = null)
    {
        $this->state = $state;
    }
}
