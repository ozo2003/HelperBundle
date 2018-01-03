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

    public $entityManager;

    public function __construct($entityManager, $object = null)
    {
        $this->entityManager = $entityManager;
        $this->connection = $this->entityManager->getConnection();
        $this->setObject($object);
    }

    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param null|object $object
     *
     * @return InsertFunctions
     */
    public function setObject($object)
    {
        $this->object = $object;
        if ($object !== null) {
            $this->metadata = $this->entityManager->getMetadataFactory()->getMetadataFor(\get_class($object));
        }

        return $this;
    }
}
