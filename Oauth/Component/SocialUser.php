<?php

namespace Sludio\HelperBundle\Oauth\Component;

interface SocialUser
{
    public function getId();

    public function getEmail();

    public function getFirstName();

    public function getLastName();

    public function getUsername();
}
