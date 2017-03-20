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

        return $kernel->getContainer()->get($kernel->getContainer()->getParameter('sludio_helper.redis.translation'));
    }

    public function postUpdate($object)
    {
        $this->getRedis()->del($object->getClassName().':translations:'.$object->getId());
        $this->getRedis()->del($object->getClassName().':translations:'.$object->getId().':checked');
    }
    
    public function getTranslationFilter($queryBuilder, $alias, $field, $value){
        if (!isset($value['value'])) {
            return;
        }
        $queryBuilder->leftJoin('Sludio:Translation', 't', 'WITH', 't.foreignKey = '.$alias.'.id');
        $queryBuilder->andWhere("t.field = 'title'");
        $queryBuilder->andWhere("t.objectClass = '".$objectClass = $this->getClass()."'");
        $queryBuilder->andWhere("t.content LIKE '%".$value['value']."%'");
        
        return true;
    }
}
