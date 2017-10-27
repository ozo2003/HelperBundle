<?php

namespace Sludio\HelperBundle\Guzzle\GuzzleHttp;

use GuzzleHttp\MessageFormatter as BaseMessageFormatter;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sludio\HelperBundle\Script\Utils\Helper;

class MessageFormatter extends BaseMessageFormatter
{
    public $template;

    private function sludioRequest($arguments)
    {
        return Psr7\str($arguments['request']);
    }

    private function sludioResponse($arguments)
    {
        return $arguments['response'] ? Psr7\str($arguments['response']) : '';
    }

    private function sludioReqHeaders($arguments)
    {
        return trim($arguments['request']->getMethod().' '.$arguments['request']->getRequestTarget()).' HTTP/'.$arguments['request']->getProtocolVersion()."\r\n".$this->headers($arguments['request']);
    }

    private function sludioResHeaders($arguments)
    {
        return $arguments['response'] ? sprintf('HTTP/%s %d %s', $arguments['response']->getProtocolVersion(), $arguments['response']->getStatusCode(), $arguments['response']->getReasonPhrase())."\r\n".$this->headers($arguments['response']) : 'NULL';
    }

    private function sludioReqBody($arguments)
    {
        return $arguments['request']->getBody();
    }

    private function sludioResBody($arguments)
    {
        return $arguments['response'] ? $arguments['response']->getBody() : 'NULL';
    }

    private function sludioTs()
    {
        return gmdate('c');
    }

    private function sludioDateIso8601()
    {
        return $this->sludioTs();
    }

    private function sludioDateCommonLog()
    {
        return date('d/M/Y:H:i:s O');
    }

    private function sludioMethod($arguments)
    {
        return $arguments['request']->getMethod();
    }

    private function sludioVersion($arguments)
    {
        return $arguments['request']->getProtocolVersion();
    }

    private function sludioUri($arguments)
    {
        return $arguments['request']->getUri();
    }

    private function sludioUrl($arguments)
    {
        return $this->sludioUri($arguments);
    }

    private function sludioTarget($arguments)
    {
        return $arguments['request']->getRequestTarget();
    }

    private function sludioReqVersion($arguments)
    {
        return $arguments['request']->getProtocolVersion();
    }

    private function sludioResVersion($arguments)
    {
        return $arguments['response'] ? $arguments['response']->getProtocolVersion() : 'NULL';
    }

    private function sludioHost($arguments)
    {
        return $arguments['request']->getHeaderLine('Host');
    }

    private function sludioHostname()
    {
        return gethostname();
    }

    private function sludioCode($arguments)
    {
        return $arguments['response'] ? $arguments['response']->getStatusCode() : 'NULL';
    }

    private function sludioPhrase($arguments)
    {
        return $arguments['response'] ? $arguments['response']->getReasonPhrase() : 'NULL';
    }

    private function sludioError($arguments)
    {
        return $arguments['error'] ? $arguments['error'] instanceof \Exception ? $arguments['error']->getMessage() : (string)$arguments['error'] : 'NULL';
    }

    /**
     * {@inheritdoc}
     */
    public function format(RequestInterface $request, ResponseInterface $response = null, $error = null)
    {
        $cache = [];

        return preg_replace_callback('/{\s*([A-Za-z_\-\.0-9]+)\s*}/', function (array $matches) use ($request, $response, $error, &$cache) {
            if (isset($cache[$matches[1]])) {
                return $cache[$matches[1]];
            }

            $cache[$matches[1]] = '';

            $method = Helper::toCamelCase('sludio_'.$matches[1]);
            if (method_exists($this, $method)) {
                $cache[$matches[1]] = $this->{$method}([
                    'request' => $request,
                    'response' => $response,
                    'error' => $error,
                ]);
            } else {
                if (strpos($matches[1], 'req_header_') === 0) {
                    $cache[$matches[1]] = $request->getHeaderLine(substr($matches[1], 11));
                } elseif (strpos($matches[1], 'res_header_') === 0) {
                    $cache[$matches[1]] = $response ? $response->getHeaderLine(substr($matches[1], 11)) : 'NULL';
                }
            }

            return $cache[$matches[1]];
        }, $this->template);
    }
}