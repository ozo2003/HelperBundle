<?php

namespace Sludio\HelperBundle\Script\Repository;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Sludio\HelperBundle\Script\Utils\Helper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Insert
{
    use ContainerAwareTrait;

    protected $manager;
    protected $connection;

    /**
     * Insert constructor.
     *
     * @param string|null|EntityManagerInterface $manager
     */
    public function __construct($manager = null)
    {

    }

}
