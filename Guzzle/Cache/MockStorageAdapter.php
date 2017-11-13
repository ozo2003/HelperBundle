<?php

namespace Sludio\HelperBundle\Guzzle\Cache;

use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sludio\HelperBundle\Guzzle\Cache\NamingStrategy\NamingStrategyInterface;
use Sludio\HelperBundle\Guzzle\Cache\NamingStrategy\SubfolderNamingStrategy;
use Sludio\HelperBundle\Guzzle\GuzzleHttp\Middleware\CacheMiddleware;
use Sludio\HelperBundle\Guzzle\GuzzleHttp\Middleware\MockMiddleware;
use Symfony\Component\Filesystem\Filesystem;

class MockStorageAdapter implements StorageAdapterInterface
{
    private $storagePath;
    /** @var NamingStrategyInterface[] */
    private $namingStrategies = [];
    private $responseHeadersBlacklist = [
        CacheMiddleware::DEBUG_HEADER,
        MockMiddleware::DEBUG_HEADER,
    ];

    /**
     * @param string $storagePath
     * @param array  $requestHeadersBlacklist
     * @param array  $responseHeadersBlacklist
     */
    public function __construct($storagePath, array $requestHeadersBlacklist = [], array $responseHeadersBlacklist = [])
    {
        $this->storagePath = $storagePath;

        $this->namingStrategies[] = new SubfolderNamingStrategy($requestHeadersBlacklist);

        if (!empty($responseHeadersBlacklist)) {
            $this->responseHeadersBlacklist = $responseHeadersBlacklist;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(RequestInterface $request)
    {
        foreach ($this->namingStrategies as $strategy) {
            if (file_exists($filename = $this->getFilename($strategy->filename($request)))) {
                return Psr7\parse_response(file_get_contents($filename));
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \RuntimeException
     */
    public function save(RequestInterface $request, ResponseInterface $response)
    {
        foreach ($this->responseHeadersBlacklist as $header) {
            $response = $response->withoutHeader($header);
        }

        [$strategy] = $this->namingStrategies;

        $filename = $this->getFilename($strategy->filename($request));

        $fileSys = new Filesystem();
        $fileSys->mkdir(\dirname($filename));

        file_put_contents($filename, Psr7\str($response));
        $response->getBody()->rewind();
    }

    /**
     * Prefixes the generated file path with the adapter's storage path.
     *
     * @param string $name
     *
     * @return string The path to the mock file
     */
    private function getFilename($name)
    {
        return $this->storagePath.'/'.$name.'.txt';
    }
}
