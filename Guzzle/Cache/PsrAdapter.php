<?php

namespace Sludio\HelperBundle\Guzzle\Cache;

use Sludio\HelperBundle\Guzzle\Cache\NamingStrategy\HashNamingStrategy;
use GuzzleHttp\Psr7\Response;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PsrAdapter implements StorageAdapterInterface
{
    private $cache;
    private $namingStrategy;
    private $ttl;

    public function __construct(CacheItemPoolInterface $cache, $ttl = 0)
    {
        $this->cache = $cache;
        $this->namingStrategy = new HashNamingStrategy();
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function fetch(RequestInterface $request)
    {
        $key = $this->namingStrategy->filename($request);

        $item = $this->cache->getItem($key);

        if ($item->isHit()) {
            $data = $item->get();

            return new Response($data['status'], $data['headers'], $data['body'], $data['version'], $data['reason']);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \RuntimeException
     */
    public function save(RequestInterface $request, ResponseInterface $response)
    {
        $key = $this->namingStrategy->filename($request);

        $item = $this->cache->getItem($key);
        $item->expiresAfter($this->ttl);
        $item->set([
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string)$response->getBody(),
            'version' => $response->getProtocolVersion(),
            'reason' => $response->getReasonPhrase(),
        ]);

        $this->cache->save($item);

        $response->getBody()->seek(0);
    }
}
