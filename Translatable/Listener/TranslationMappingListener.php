<?php

namespace Sludio\HelperBundle\Translatable\Listener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

class TranslationMappingListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        $classMetadata = $eventArgs->getClassMetadata();
        $table = $classMetadata->table;
        $oldName = $table['name'];
        $param = $kernel->getContainer()->getParameter('sludio_helper.translatable.table', 'sludio_helper_translation');
        if ($oldName === 'sludio_helper_translation' && $param !== $oldName) {
            $table['name'] = $param;
        }
        $classMetadata->setPrimaryTable($table);
    }
}
