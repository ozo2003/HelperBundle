<?php

namespace Sludio\HelperBundle\Openidconnect\Provider;

use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\AbstractProvider;
use Sludio\HelperBundle\Script\Exception\ErrorException;

abstract class BaseProvider extends AbstractProvider
{
    /**
     * @inheritdoc
     */
    public function getAccessToken($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'redirect_uri' => $this->redirectUri,
        ];

        $params = $grant->prepareRequestParameters($params, $options);
        $request = $this->getAccessTokenRequest($params);
        $response = $this->getResponse($request);
        if(!is_array($response)){
            throw new ErrorException('error_invalid_request');
        }
        $prepared = $this->prepareAccessTokenResponse($response);

        return $this->createAccessToken($prepared, $grant);
    }

    protected function createAccessToken(array $response, AbstractGrant $grant)
    {
        return new AccessToken($response);
    }
}
