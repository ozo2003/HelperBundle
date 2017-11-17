<?php

namespace Sludio\HelperBundle\Guzzle\GuzzleHttp\Middleware;

use Sludio\HelperBundle\Guzzle\Cache\StorageAdapterInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;

class MockMiddleware extends CacheMiddleware
{
    const DEBUG_HEADER = 'X-Guzzle-Mock';
    const DEBUG_HEADER_HIT = 'REPLAY';
    const DEBUG_HEADER_MISS = 'RECORD';

    private $mode;

    public function __construct(StorageAdapterInterface $adapter, $mode, $debug = false)
    {
        parent::__construct($adapter, $debug);

        $this->mode = $mode;
    }

    public function __invoke(callable $handler)
    {
        return function(RequestInterface $request, array $options) use ($handler) {
            if ('record' === $this->mode) {
                return $this->handleSave($handler, $request, $options);
            }

            if (null === $response = $this->adapter->fetch($request)) {
                return new RejectedPromise(sprintf('Record not found for request: %s %s', $request->getMethod(), $request->getUri()));
            }

            $response = $this->addDebugHeader($response, 'REPLAY');

            return new FulfilledPromise($response);
        };
    }
}
