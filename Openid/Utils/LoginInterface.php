<?php

namespace Sludio\HelperBundle\Openid\Utils;

interface LoginInterface
{
    public function url($return);

    public function validate();

    public function redirect();

    public function fetchUser();
}
