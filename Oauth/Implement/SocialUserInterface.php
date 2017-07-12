<?php

namespace Sludio\HelperBundle\Oauth\Implement;

interface SocialUserInterface
{
    public function getId();

    public function getEmail();

    public function getFirstName();

    public function getLastName();

    public function getUsername();
}
