<?php

namespace Sludio\HelperBundle\Openid\Component;

interface Loginable
{
    public function url($return);

    public function validate();

    public function redirect();

    public function fetchUser();
}
