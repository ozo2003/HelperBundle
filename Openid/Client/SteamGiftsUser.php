<?php

namespace Sludio\HelperBundle\Openid\Client;

use Sludio\HelperBundle\Oauth\Component\SocialUserInterface;

class SteamGiftsUser implements SocialUserInterface
{
    public function getId()
    {

    }

    public function getEmail()
    {

    }

    public function getFirstName()
    {

    }

    public function getLastName()
    {

    }

    public function getUsername()
    {

    }

    /**
     * @var bool
     */
    protected $returnsEmail = false;

    /**
     * @return bool
     */
    public function returnsEmail()
    {
        return $this->returnsEmail;
    }
}
