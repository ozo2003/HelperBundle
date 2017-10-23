<?php

namespace Sludio\HelperBundle\Guzzle\Cache\NamingStrategy;

use Psr\Http\Message\RequestInterface;

class HashNamingStrategy implements NamingStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function filename(RequestInterface $request)
    {
        return md5(serialize([
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'headers' => $request->getHeaders(),
        ]));
    }
}