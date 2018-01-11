<?php

namespace Sludio\HelperBundle\Guzzle\GuzzleHttp\Middleware;

use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;
use Sludio\HelperBundle\Guzzle\GuzzleHttp\History\History;

class HistoryMiddleware
{
    private $container;

    public function __construct(History $container)
    {
        $this->container = $container;
    }

    public function __invoke(callable $handler)
    {
        return function(RequestInterface $request, array $options) use ($handler) {
            return $handler($request, $options)->then(function($response) use ($request, $options) {
                $this->container->mergeInfo($request, [
                    'response' => $response,
                    'error' => null,
                    'options' => $options,
                    'info' => [],
                ]);

                return $response;
            }, function($reason) use ($request, $options) {
                $this->container->mergeInfo($request, [
                    'response' => null,
                    'error' => $reason,
                    'options' => $options,
                    'info' => [],
                ]);

                return new RejectedPromise($reason);
            });
        };
    }
}
