<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Draugiem;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Sludio\HelperBundle\Oauth\Component\SocialUser;

class DraugiemUser implements ResourceOwnerInterface, SocialUser
{
    /**
     * @var array
     */
    protected $response;

    protected $userData;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $username;

    /**
     * @param  array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
        $this->userData = reset($this->response['users']);

        $this->id = intval($this->response['uid']);

        $this->firstName = $this->getField('name');

        $this->lastName = $this->getField('surname');

        $this->username = preg_replace('/[^a-z\d]/i', '', $this->getField('url'));
    }

    /**
     * Returns all the data obtained about the user.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
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

    /**
     * Get the value of Id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the value of First Name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Get the value of Last Name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Get the value of Username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
}
