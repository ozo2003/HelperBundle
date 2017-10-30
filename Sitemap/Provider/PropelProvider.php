<?php

namespace Sludio\HelperBundle\Sitemap\Provider;

use Symfony\Component\Routing\RouterInterface;
use Sludio\HelperBundle\Sitemap\Sitemap;

class PropelProvider extends AbstractProvider
{
    protected $router = null;

    protected $options = [
        'model' => null,
        'loc' => [],
        'filters' => [],
        'lastmod' => null,
        'priority' => null,
        'changefreq' => null,
    ];

    /**
     * Constructor
     *
     * @param RouterInterface $router  The application router.
     * @param array           $options The options (see the class comment).
     */
    public function __construct(RouterInterface $router, array $options)
    {
        parent::__construct($router, $options);

        if (!class_exists($options['model'])) {
            throw new \LogicException('Can\'t find class '.$options['model']);
        }
    }

    /**
     * Populate a sitemap using a Propel model.
     *
     * @param Sitemap $sitemap The current sitemap.
     */
    public function populate(Sitemap $sitemap)
    {
        $query = $this->getQuery($this->options['model']);

        // apply filters on the query
        foreach ($this->options['filters'] as $filter) {
            $query->$filter();
        }

        // and populate the sitemap!
        foreach ($query->find() as $result) {
            $sitemap->add($this->resultToUrl($result));
        }
    }

    protected function getQuery($model)
    {
        return \PropelQuery::from($model)->setFormatter('PropelOnDemandFormatter');
    }
}