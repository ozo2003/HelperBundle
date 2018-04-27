<?php

namespace Sludio\HelperBundle\Script\Repository;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Sludio\HelperBundle\Script\Utils\Helper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Insert
{
    protected $manager;

    protected $connection;

    /**
     * Insert constructor.
     *
     * @param ContainerInterface                 $container
     * @param string|null|EntityManagerInterface $manager
     */
    public function __construct(ContainerInterface $container, $manager = null)
    {

    }

}
