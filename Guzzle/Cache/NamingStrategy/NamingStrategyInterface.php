<?php

namespace Sludio\HelperBundle\Guzzle\Cache\NamingStrategy;

use Psr\Http\Message\RequestInterface;

interface NamingStrategyInterface
{
    public function filename(RequestInterface $request);
}