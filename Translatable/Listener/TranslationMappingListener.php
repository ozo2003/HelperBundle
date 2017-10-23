<?php

namespace Sludio\HelperBundle\Translatable\Listener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class TranslationMappingListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        global $kernel;

        if ('AppCache' === get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        $classMetadata = $eventArgs->getClassMetadata();
        $oldName = $classMetadata->getTableName();
        $param = $kernel->getContainer()->getParameter('sludio_helper.translatable.table');
        if ($oldName === 'sludio_helper_translation' && $param !== $oldName) {
            $classMetadata->setPrimaryTable(['name' => $param]);
        }
    }
}
