<?php

namespace Sludio\HelperBundle\Openidconnect\Provider;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Sludio\HelperBundle\Openidconnect\Implement\UriInterface;

class Uri implements UriInterface
{
    private $url;
    private $base;

    protected $params;
    protected $urlParams;

    public function __construct(array $options, array $additional = [])
    {
        $this->base = rtrim($additional['base_uri'], '/') . '/';
        unset($additional['base_uri']);

        $this->params = !empty($options['params']) ? $options['params'] : [];
        $this->urlParams = !empty($options['url_params']) ? array_merge($options['url_params'], $additional) : $additional;
    }

    public function redirect()
    {
        return new RedirectResponse($this->getUrl());
    }

    private function buildUrl()
    {
        $url = $this->base;
        if (!empty($this->params)) {
            $url .= implode('/', $this->params);
        }
        if (!empty($this->urlParams)) {
            $params = http_build_query($this->urlParams);
            $url .= '?' . $params;
        }
        $url = urldecode($url);
        $this->setUrl($url);
    }

    /**
     * Get the value of Url
     *
     * @return mixed
     */
    public function getUrl()
    {
        $this->buildUrl();
        return $this->url;
    }

    /**
     * Set the value of Url
     *
     * @param mixed url
     *
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function addParam($value)
    {
        $this->params[] = $value;
    }

    public function addUrlParam($name, $value)
    {
        $this->urlParams[$name] = $value;
    }
}
