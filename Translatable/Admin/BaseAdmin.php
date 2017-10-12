<?php

namespace Sludio\HelperBundle\Translatable\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class BaseAdmin extends AbstractAdmin
{
    protected function getRedis()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        return $kernel->getContainer()->get('snc_redis.'.$kernel->getContainer()
                ->getParameter('sludio_helper.redis.translation'))
            ;
    }

    public function postUpdate($object)
    {
        $this->getRedis()->del($object->getClassName().':translations:'.$object->getId());
        $this->getRedis()->del($object->getClassName().':translations:'.$object->getId().':checked');
    }

    public function getTranslationFilter($queryBuilder, $alias, $field, $value)
    {
        if (!isset($value['value'])) {
            return;
        }
        $queryBuilder->leftJoin('Sludio:Translation', 't', 'WITH', 't.foreignKey = '.$alias.'.id');
        $queryBuilder->andWhere("t.field = '$field'");
        $queryBuilder->andWhere("t.objectClass = '".$objectClass = $this->getClass()."'");
        $queryBuilder->andWhere("t.content LIKE '%".$value['value']."%'");
        $queryBuilder->setFirstResult(0);

        return true;
    }
}
