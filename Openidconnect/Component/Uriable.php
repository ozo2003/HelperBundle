<?php

namespace Sludio\HelperBundle\Openidconnect\Component;

interface Uriable
{
    public function getUrl();

    public function redirect();
}
