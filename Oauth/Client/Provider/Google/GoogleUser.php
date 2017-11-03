<?php

namespace Sludio\HelperBundle\Oauth\Client\Provider\Google;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Sludio\HelperBundle\Oauth\Component\SocialUserInterface;

class GoogleUser implements ResourceOwnerInterface, SocialUserInterface
{
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
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;

        $this->id = intval($this->response['id']);

        if (!empty($this->response['emails'])) {
            $this->email = $this->response['emails'][0]['value'];
        }

        $this->firstName = $this->response['name']['givenName'];

        $this->lastName = $this->response['name']['familyName'];

        $username = explode('@', $this->email);
        $username = preg_replace('/[^a-z\d]/i', '', $username[0]);
        $this->username = $username;
    }

    /**
     * Get preferred display name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->response['displayName'];
    }

    /**
     * Get avatar image URL.
     *
     * @return string|null
     */
    public function getAvatar()
    {
        if (!empty($this->response['image']['url'])) {
            return $this->response['image']['url'];
        }
    }

    /**
     * Get user data as an array.
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
