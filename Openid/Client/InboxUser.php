<?php

namespace Sludio\HelperBundle\Openid\Client;

use Sludio\HelperBundle\Oauth\Implement\SocialUserInterface;

class InboxUser implements SocialUserInterface
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

    /**
     * Returns a field from the Graph node data.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    private function getField($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function getId()
    {
        return $this->getField('openid_sreg_email');
    }

    public function getEmail()
    {
        return $this->getField('openid_sreg_email');
    }

    public function getFirstName()
    {
        $name = $this->getField('openid_sreg_fullname');
        $data = explode(' ', $name, 2);

        return $data[0];
    }

    public function getLastName()
    {
        $name = $this->getField('openid_sreg_fullname');
        $data = explode(' ', $name, 2);

        return $data[1];
    }
}
