<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Twitter;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Sludio\HelperBundle\Oauth\Implement\SocialUserInterface;

class TwitterUser implements ResourceOwnerInterface, SocialUserInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param  array $response
     */
    public function __construct(array $response)
    {
        $this->data = $response;
    }

    public function getId()
    {
        return intval($this->data['user_id']);
    }

    public function getEmail()
    {
        return null;
    }

    public function getFirstName()
    {
        return null;
    }

    public function getLastName()
    {
        return null;
    }

    /**
     * Returns all the data obtained about the user.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}
