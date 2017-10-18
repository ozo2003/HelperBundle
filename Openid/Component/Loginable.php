<?php

namespace Sludio\HelperBundle\Openid\Component;

interface Loginable
{
    public function urlPath($return);

    public function validate();

    public function redirect();

    public function fetchUser();
}
