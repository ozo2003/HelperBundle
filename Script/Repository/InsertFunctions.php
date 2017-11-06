<?php

namespace Sludio\HelperBundle\Script\Repository;

use Sludio\HelperBundle\Script\Utils\Helper;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

class InsertFunctions
{
    protected $connection;

    /**
     * @var \stdClass
     */
    protected $object;

    /**
     * @var ClassMetadata
     */
    protected $metadata;

    public $entityManager;

    public function __construct($entityManager, \stdClass $object = null)
    {
        $this->entityManager = $entityManager;
        $this->connection = $this->entityManager->getConnection();
        $this->setObject($object);
    }

    /**
     * @return null
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param null $object
     *
     * @return InsertFunctions
     */
    public function setObject($object)
    {
        $this->object = $object;
        if ($object !== null) {
            $this->metadata = $this->entityManager->getMetadataFactory()->getMetadataFor(get_class($object));
        }

        return $this;
    }
}
