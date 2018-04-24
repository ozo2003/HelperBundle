<?php

namespace Sludio\HelperBundle\Openid\Client;

use Sludio\HelperBundle\Oauth\Component\SocialUserInterface;
use Sludio\HelperBundle\Oauth\Component\HaveEmailInterface;

class SteamGiftsUser implements SocialUserInterface, HaveEmailInterface
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
