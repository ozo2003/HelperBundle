<?php

namespace Sludio\HelperBundle\Guzzle\GuzzleHttp;

use GuzzleHttp\MessageFormatter as BaseMessageFormatter;
use GuzzleHttp\Psr7;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sludio\HelperBundle\Script\Utils\Helper;

class MessageFormatter extends BaseMessageFormatter
{
    public $template;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var \Exception
     */
    protected $error;

    protected function sludioRequest()
    {
        return Psr7\str($this->request);
    }

    protected function sludioResponse()
    {
        return $this->response ? Psr7\str($this->response) : '';
    }

    protected function sludioReqHeaders()
    {
        return trim($this->request->getMethod().' '.(string)$this->request).' HTTP/'.$this->request->getProtocolVersion()."\r\n".$this->headers($this->request);
    }

    protected function sludioResHeaders()
    {
        return $this->response ? sprintf('HTTP/%s %d %s', $this->response->getProtocolVersion(), $this->response->getStatusCode(), $this->response->getReasonPhrase())."\r\n".$this->headers($this->response) : 'NULL';
    }

    protected function sludioReqBody()
    {
        return $this->request->getBody();
    }

    protected function sludioResBody()
    {
        return $this->response ? $this->response->getBody() : 'NULL';
    }

    protected function sludioTs()
    {
        return gmdate('c');
    }

    protected function sludioDateIso8601()
    {
        return $this->sludioTs();
    }

    protected function sludioDateCommonLog()
    {
        return date('d/M/Y:H:i:s O');
    }

    protected function sludioMethod()
    {
        return $this->request->getMethod();
    }

    protected function sludioVersion()
    {
        return $this->request->getProtocolVersion();
    }

    protected function sludioUri()
    {
        return $this->request->getUri();
    }

    protected function sludioUrl()
    {
        return $this->sludioUri();
    }

    protected function sludioTarget()
    {
        return $this->request->getRequestTarget();
    }

    protected function sludioReqVersion()
    {
        return $this->request->getProtocolVersion();
    }

    protected function sludioResVersion()
    {
        return $this->response ? $this->response->getProtocolVersion() : 'NULL';
    }

    protected function sludioHost()
    {
        return $this->request->getHeaderLine('Host');
    }

    protected function sludioHostname()
    {
        return gethostname();
    }

    protected function sludioCode()
    {
        return $this->response ? $this->response->getStatusCode() : 'NULL';
    }

    protected function sludioPhrase()
    {
        return $this->response ? $this->response->getReasonPhrase() : 'NULL';
    }

    protected function sludioError()
    {
        if ($this->error) {
            if ($this->error instanceof \Exception) {
                return $this->error->getMessage();
            }

            return (string)$this->error;
        }
        
        return 'NULL';
    }

    /**
     * {@inheritdoc}
     */
    public function format(RequestInterface $request, ResponseInterface $response = null, \Exception $error = null)
    {
        $cache = [];

        return preg_replace_callback('/{\s*([A-Za-z_\-\.0-9]+)\s*}/', function(array $matches) use ($request, $response, $error, &$cache) {
            if (isset($cache[$matches[1]])) {
                return $cache[$matches[1]];
            }

            $cache[$matches[1]] = '';

            $method = Helper::toCamelCase('sludio_'.$matches[1]);
            if (method_exists($this, $method)) {
                $this->request = $request;
                $this->response = $response;
                $this->error = $error;

                $cache[$matches[1]] = $this->{$method}();
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

    private function headers(MessageInterface $message)
    {
        $result = '';
        foreach ($message->getHeaders() as $name => $values) {
            $result .= $name.': '.implode(', ', $values)."\r\n";
        }

        return trim($result);
    }
}
