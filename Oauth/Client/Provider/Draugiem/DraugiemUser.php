<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Draugiem;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Sludio\HelperBundle\Oauth\Implement\SocialUserInterface;

class DraugiemUser implements ResourceOwnerInterface, SocialUserInterface
{
    /**
     * @var array
     */
    protected $data;
    protected $userData;

    /**
     * @param  array $response
     */
    public function __construct(array $response)
    {
        $this->data = $response;
        $this->userData = reset($this->data['users']);
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

    /**
     * Returns a field from the Graph node data.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    private function getField($key)
    {
        return isset($this->userData[$key]) ? $this->userData[$key] : null;
    }

    public function getId(){
        return intval($this->data['uid']);
    }

    public function getEmail(){
        return '';
    }

    public function getFirstName(){
        return $this->getField('name');
    }

    public function getLastName(){
        return $this->getField('surname');
    }
}
