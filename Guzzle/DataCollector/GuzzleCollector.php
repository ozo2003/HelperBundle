<?php

namespace Sludio\HelperBundle\Guzzle\DataCollector;

use GuzzleHttp\Exception\RequestException;
use Namshi\Cuzzle\Formatter\CurlFormatter;
use Psr\Http\Message\StreamInterface;
use Sludio\HelperBundle\Guzzle\GuzzleHttp\History\History;
use Sludio\HelperBundle\Guzzle\GuzzleHttp\Middleware\CacheMiddleware;
use Sludio\HelperBundle\Guzzle\GuzzleHttp\Middleware\MockMiddleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class GuzzleCollector extends DataCollector
{
    const MAX_BODY_SIZE = 0x10000;

    private $maxBodySize;
    private $history;
    private $curlFormatter;

    /**
     * Constructor.
     *
     * @param int          $maxBodySize The max body size to store in the profiler storage
     * @param History|null $history
     */
    public function __construct($maxBodySize = self::MAX_BODY_SIZE, History $history = null)
    {
        $this->maxBodySize = $maxBodySize;
        $this->history = $history ?: new History();

        if (class_exists(CurlFormatter::class)) {
            $this->curlFormatter = new CurlFormatter();
        }

        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $data = [];

        foreach ($this->history as $historyRequest) {
            /* @var \Psr\Http\Message\RequestInterface $historyRequest */
            $transaction = $this->history[$historyRequest];
            /* @var \Psr\Http\Message\ResponseInterface $historyResponse */
            $historyResponse = $transaction['response'];
            /* @var \Exception $error */
            $error = $transaction['error'];
            /* @var array $info */
            $info = $transaction['info'];

            $req = [
                'request' => [
                    'method' => $historyRequest->getMethod(),
                    'version' => $historyRequest->getProtocolVersion(),
                    'headers' => $historyRequest->getHeaders(),
                    'body' => $this->cropContent($historyRequest->getBody()),
                ],
                'info' => $info,
                'uri' => urldecode($historyRequest->getUri()),
                'httpCode' => 0,
                'error' => null,
            ];

            if ($this->curlFormatter && $historyRequest->getBody()->getSize() <= $this->maxBodySize) {
                $req['curl'] = $this->curlFormatter->format($historyRequest);
            }

            if ($historyResponse) {
                $req['response'] = [
                    'reasonPhrase' => $historyResponse->getReasonPhrase(),
                    'headers' => $historyResponse->getHeaders(),
                    'body' => $this->cropContent($historyResponse->getBody()),
                ];

                $req['httpCode'] = $historyResponse->getStatusCode();

                if ($historyResponse->hasHeader(CacheMiddleware::DEBUG_HEADER)) {
                    $req['cache'] = $historyResponse->getHeaderLine(CacheMiddleware::DEBUG_HEADER);
                }

                if ($historyResponse->hasHeader(MockMiddleware::DEBUG_HEADER)) {
                    $req['mock'] = $historyResponse->getHeaderLine(MockMiddleware::DEBUG_HEADER);
                }
            }

            if ($error && $error instanceof RequestException) {
                $req['error'] = [
                    'message' => $error->getMessage(),
                    'line' => $error->getLine(),
                    'file' => $error->getFile(),
                    'code' => $error->getCode(),
                    'trace' => $error->getTraceAsString(),
                ];
            }

            $data[] = $req;
        }

        $this->data = $data;
    }

    private function cropContent(StreamInterface $stream = null)
    {
        if (null === $stream) {
            return '';
        }

        if ($stream->getSize() <= $this->maxBodySize) {
            return (string)$stream;
        }

        $stream->seek(0);

        return '(partial content)'.$stream->read($this->maxBodySize).'(...)';
    }

    public function getErrors()
    {
        return array_filter($this->data, function($call) {
            return 0 === $call['httpCode'] || $call['httpCode'] >= 400;
        });
    }

    public function getTotalTime()
    {
        return array_sum(array_map(function($call) {
            return isset($call['info']['total_time']) ? $call['info']['total_time'] : 0;
        }, $this->data));
    }

    public function getCalls()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'guzzle';
    }
}
