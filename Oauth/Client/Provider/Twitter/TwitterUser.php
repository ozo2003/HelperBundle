<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Twitter;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Sludio\HelperBundle\Oauth\Component\HaveEmailInterface;
use Sludio\HelperBundle\Oauth\Component\SocialUserInterface;

class TwitterUser implements ResourceOwnerInterface, SocialUserInterface, HaveEmailInterface
{
    const RETURNS_EMAIL = false;

    /**
     * @var array
     */
    protected $response;

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
     * @return bool
     */
    public function returnsEmail()
    {
        return self::RETURNS_EMAIL;
    }

    /**
     * @param  array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;

        $this->id = (int)$this->response['user_id'];

        if (isset($this->response['email'])) {
            $this->email = $this->response['email'];
        }

        $this->username = preg_replace('/[^a-z\d]/i', '', $this->response['screen_name']);
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
