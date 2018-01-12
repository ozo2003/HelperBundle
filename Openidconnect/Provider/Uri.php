<?php

namespace Sludio\HelperBundle\Openidconnect\Provider;

use Sludio\HelperBundle\Openidconnect\Component\Uriable;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Uri implements Uriable
{
    protected $params;
    protected $urlParams;
    protected $url;
    protected $base;

    public function __construct(array $options, array $additional = [], $useSession = false, $method = OpenIDConnectProvider::METHOD_POST)
    {
        $this->base = rtrim($additional['base_uri'], '/').'/';
        unset($additional['base_uri']);

        $this->params = !empty($options['params']) ? $options['params'] : [];

        if ($method === OpenIDConnectProvider::METHOD_GET) {
            if (isset($options['url_params']['post_logout_redirect_uri'])) {
                $options['url_params']['post_logout_redirect_uri'] = $additional['redirect_uri'];
                unset($additional['redirect_uri']);
            }

            if (isset($options['url_params']['id_token_hint'], $_SESSION['id_token'])) {
                if ($useSession === false) {
                    throw new \InvalidArgumentException(sprintf('"%s" parameter must be set in order to use id_token_hint', 'use_session'));
                }
                $additional['id_token_hint'] = $_SESSION['id_token'];
                unset($options['url_params']['id_token_hint']);
            }
            $this->urlParams = !empty($options['url_params']) ? array_merge($options['url_params'], $additional) : $additional;
        }
    }

    public function redirect()
    {
        return new RedirectResponse($this->getUrl());
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
     * @param mixed $url
     *
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    private function buildUrl()
    {
        $url = $this->base;
        if (!empty($this->params)) {
            $url .= implode('/', $this->params);
        }
        if (!empty($this->urlParams)) {
            $params = http_build_query($this->urlParams);
            $url .= '?'.$params;
        }
        $url = urldecode($url);
        $this->setUrl($url);
    }

    public function addParam($value)
    {
        $this->params[] = $value;
    }

    public function addUrlParam($name, $value)
    {
        $this->urlParams[$name] = $value;
    }

    /**
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }
}
