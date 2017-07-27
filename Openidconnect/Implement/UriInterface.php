<?php

namespace Sludio\HelperBundle\Openidconnect\Implement;

interface UriInterface
{
    public function getUrl();

    public function redirect();
}
