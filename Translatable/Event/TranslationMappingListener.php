<?php

namespace Sludio\HelperBundle\Translatable\Event;

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
        if($oldName == 'sludio_helper_translation' && $kernel->getContainer()->hasParameter('sludio_helper.translatable.table')){
            $table['name'] = $kernel->getContainer()->getParameter('sludio_helper.translatable.table');
        }
        $classMetadata->setPrimaryTable($table);
    }
}
