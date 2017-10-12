<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Custom;

use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class CustomResourceOwner extends GenericResourceOwner
{
    use ArrayAccessorTrait;

    protected $response;

    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    public function toArray()
    {
        return $this->response;
    }
}
