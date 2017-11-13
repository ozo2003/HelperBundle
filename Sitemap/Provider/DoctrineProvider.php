<?php

namespace Sludio\HelperBundle\Sitemap\Provider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Routing\RouterInterface;
use Sludio\HelperBundle\Sitemap\Sitemap;

class DoctrineProvider extends AbstractProvider
{
    protected $entityManager;

    /**
     * Constructor
     *
     * @param Entitymanager   $entityManager Doctrine entity manager.
     * @param RouterInterface $router        The application router.
     * @param array           $options       The options (see the class comment).
     */
    public function __construct(EntityManager $entityManager, RouterInterface $router, array $options)
    {
        parent::__construct($router, $options);

        $this->options = [
            'entity' => null,
            'loc' => [],
            'query_method' => null,
            'lastmod' => null,
            'priority' => null,
            'changefreq' => null,
        ];

        $this->entityManager = $entityManager;
    }

    /**
     * Populate a sitemap using a Doctrine entity.
     *
     * @param Sitemap $sitemap The current sitemap.
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function populate(Sitemap $sitemap)
    {
        $query = $this->getQuery($this->options['entity'], $this->options['query_method']);
        $results = $query->iterate();

        // and populate the sitemap!
        while (($result = $results->next()) !== false) {
            $sitemap->add($this->resultToUrl($result[0]));

            $this->entityManager->detach($result[0]);
        }
    }

    protected function getQuery($entity, $method = null)
    {
        $repo = $this->entityManager->getRepository($entity);

        if ($method !== null) {
            $query = $repo->$method();
        } else {
            $query = $repo->createQueryBuilder('o')->getQuery();
        }

        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }

        if (!$query instanceof Query) {
            throw new \RuntimeException(sprintf('Expected instance of Query, got %s (see method %s:%s)', \get_class($query), $entity, $method));
        }

        return $query;
    }
}
