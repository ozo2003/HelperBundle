<?php

namespace Sludio\HelperBundle\Script\Utils;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class StreamResponse extends Response
{
    const BUFFER_SIZE = 4096;

    private $bufferSize;

    public function __construct(ResponseInterface $response, $bufferSize = self::BUFFER_SIZE)
    {
        parent::__construct(null, $response->getStatusCode(), $response->getHeaders());

        $this->content = $response->getBody();
        $this->bufferSize = $bufferSize;
    }

    /**
     * @return $this|void
     *
     * @throws \RuntimeException
     */
    public function sendContent()
    {
        $chunked = $this->headers->has('Transfer-Encoding');
        $this->content->seek(0);

        for (; ;) {
            $chunk = $this->content->read($this->bufferSize);

            if ($chunked) {
                echo sprintf("%x\r\n", \strlen($chunk));
            }

            echo $chunk;

            if ($chunked) {
                echo "\r\n";
            }

            flush();

            if (!$chunk) {
                return;
            }
        }
    }

    public function getContent()
    {
        return false;
    }
}
