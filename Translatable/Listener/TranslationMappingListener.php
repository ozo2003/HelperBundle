<?php

namespace Sludio\HelperBundle\Translatable\Listener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class TranslationMappingListener
{
    protected $tableName;

    /**
     * TranslationMappingListener constructor.
     *
     * @param $tableName
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $oldName = $classMetadata->getTableName();
        if ($oldName === 'sludio_helper_translation' && $this->tableName !== $oldName) {
            $classMetadata->setPrimaryTable(['name' => $this->tableName]);
        }
    }
}
