<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Custom;

use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class CustomResourceOwner extends GenericResourceOwner
{
    use ArrayAccessorTrait;

    protected $domain;

    public function __construct(array $response, $resourceOwnerId)
    {
        parent::__construct($response, $resourceOwnerId);
        $this->response = $response;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }
}
