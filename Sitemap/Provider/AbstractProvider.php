<?php

namespace Sludio\HelperBundle\Sitemap\Provider;

use Symfony\Component\Routing\RouterInterface;
use Sludio\HelperBundle\Sitemap\Entity\Url;

/**
 * Abstract class containing common methods used by Propel and Doctrine providers.
 */
abstract class AbstractProvider implements ProviderInterface
{
    protected $router;

    /** @var array */
    protected $options = [
        'loc' => [],
        'lastmod' => null,
        'priority' => null,
        'changefreq' => null,
    ];

    /**
     * AbstractProvider constructor.
     *
     * @param RouterInterface $router
     * @param array           $options
     */
    public function __construct(RouterInterface $router, array $options)
    {
        $this->router = $router;
        $this->options = array_merge($this->options, $options);
    }

    protected function resultToUrl($result)
    {
        $url = new Url();
        $url->setLoc($this->getResultLoc($result));

        if ($this->options['priority'] !== null) {
            $url->setPriority($this->options['priority']);
        }

        if ($this->options['changefreq'] !== null) {
            $url->setChangefreq($this->options['changefreq']);
        }

        if ($this->options['lastmod'] !== null) {
            $url->setLastmod($this->getColumnValue($result, $this->options['lastmod']));
        }

        return $url;
    }

    protected function getResultLoc($result)
    {
        $route = $this->options['loc']['route'];
        $params = [];

        if (!isset($this->options['loc']['params'])) {
            $this->options['loc']['params'] = [];
        }

        foreach ($this->options['loc']['params'] as $key => $column) {
            $params[$key] = $this->getColumnValue($result, $column);
        }

        return $this->router->generate($route, $params);
    }

    protected function getColumnValue($result, $column)
    {
        $method = 'get'.ucfirst($column);

        if (!method_exists($result, $method)) {
            throw new \RuntimeException(sprintf('"%s" method not found in "%s"', $method, \get_class($result)));
        }

        return $result->$method();
    }
}
