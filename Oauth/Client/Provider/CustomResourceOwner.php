<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider;

use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Provider\GenericResourceOwner;

class CustomResourceOwner extends GenericResourceOwner
{
    use ArrayAccessorTrait;
    
    protected $response;
    
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }
    
    public function toArray()
    {
        return $this->response;
    }
}
