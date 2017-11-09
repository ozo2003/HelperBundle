<?php

namespace Sludio\HelperBundle\Sitemap\Provider;

use Sludio\HelperBundle\Sitemap\Sitemap;
use Sludio\HelperBundle\Sitemap\Entity\Url;
use Symfony\Component\Routing\RouterInterface;

class SimpleProvider extends AbstractProvider
{
    protected $defaultRoute = [
        'params' => [],
        'priority' => null,
        'changefreq' => null,
        'lastmod' => null,
    ];

    public function populate(Sitemap $sitemap)
    {
        foreach ($this->options['routes'] as $route) {
            $route = array_merge($this->defaultRoute, $route);

            $url = new Url();
            $url->setLoc($this->router->generate($route['name'], $route['params']));
            $url->setChangefreq($route['changefreq'] ?: $this->options['changefreq']);
            $url->setLastmod($route['lastmod'] ?: $this->options['lastmod']);
            $url->setPriority($route['priority'] ?: $this->options['priority']);
            $sitemap->add($url);
        }
    }

    public function __construct(RouterInterface $router, array $options)
    {
        parent::__construct($router, $options);

        $this->options = [
            'routes' => [],
            'lastmod' => null,
            'priority' => null,
            'changefreq' => null,
        ];
    }
}
