<?php

namespace Sludio\HelperBundle\Translatable\Admin;

use Sonata\AdminBundle\Admin\Admin;

class BaseAdmin extends Admin
{
    protected function getRedis()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        return $kernel->getContainer()->get($kernel->getContainer()->getParameter('sludio_helper.translation_redis'));
    }

    public function postUpdate($object)
    {
        $this->getRedis()->del($object->getClassName().':translations:'.$object->getId());
        $this->getRedis()->del($object->getClassName().':translations:'.$object->getId().':checked');
    }
}
