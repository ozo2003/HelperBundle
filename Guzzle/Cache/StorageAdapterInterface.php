<?php

namespace Sludio\HelperBundle\Guzzle\Cache;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface StorageAdapterInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return null|ResponseInterface
     */
    public function fetch(RequestInterface $request);

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     */
    public function save(RequestInterface $request, ResponseInterface $response);
}
