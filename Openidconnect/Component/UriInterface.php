<?php

namespace Sludio\HelperBundle\Openidconnect\Component;

interface UriInterface
{
    public function getUrl();

    public function redirect();
}
