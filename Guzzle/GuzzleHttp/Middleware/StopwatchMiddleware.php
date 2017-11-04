<?php

namespace Sludio\HelperBundle\Guzzle\GuzzleHttp\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class StopwatchMiddleware
{
    private $stopwatch;
    private $increments = [];

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function __invoke(callable $handler)
    {
        return function(RequestInterface $request, array $options) use ($handler) {
            $key = sprintf('%s %s', $request->getMethod(), (string)$request->getUri());

            if (!isset($this->increments[$key])) {
                $this->increments[$key] = 1;
            } else {
                ++$this->increments[$key];

                $key .= ' ('.$this->increments[$key].')';
            }

            $this->stopwatch->start($key);

            return $handler($request, $options)->then(function(ResponseInterface $response) use ($key) {
                $this->stopwatch->stop($key);

                return $response;
            });
        };
    }
}
