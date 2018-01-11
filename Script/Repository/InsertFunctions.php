<?php

namespace Sludio\HelperBundle\Script\Repository;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class InsertFunctions
{
    protected $connection;

    protected $object;

    /**
     * @var ClassMetadata
     */
    protected $metadata;

    public $doctrine;

    public $entityManager;

    public function __construct($doctrine, $defaultManager)
    {
        $this->doctrine = $doctrine;
        $this->connection = $this->doctrine->getManager('default')->getConnection();
        $this->setManager($defaultManager);
    }

    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param object $object
     */
    public function setObject($object)
    {
        $this->object = $object;
        $this->metadata = $this->entityManager->getMetadataFactory()->getMetadataFor(\get_class($object));
    }

    public function setManager($manager)
    {
        if (\is_object($manager)) {
            $this->entityManager = $manager;
        } else {
            $this->entityManager = $this->doctrine->getManager($manager);
        }
    }
}
