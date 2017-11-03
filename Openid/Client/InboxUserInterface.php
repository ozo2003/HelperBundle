<?php

namespace Sludio\HelperBundle\Openid\Client;

use Sludio\HelperBundle\Oauth\Component\SocialUserInterface;

class InboxUserInterface implements SocialUserInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @var integer
     */
    protected $originalId;

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
    public function __construct(array $response, $id = null)
    {
        $this->response = $response;
        $this->originalId = $id;

        $this->id = $this->getField('openid_sreg_email');

        $this->email = $this->getField('openid_sreg_email');

        $name = $this->getField('openid_sreg_fullname');
        $data = explode(' ', $name, 2);

        $this->firstName = $data[0];

        $this->lastName = $data[1];

        $username = explode('@', $this->originalId ?: $this->email);
        $username = preg_replace('/[^a-z\d]/i', '', $username[0]);
        $this->username = $username;
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
        return isset($this->response[$key]) ? $this->response[$key] : null;
    }

    /**
     * Get the value of Original Id
     *
     * @return integer
     */
    public function getOriginalId()
    {
        return $this->originalId;
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
