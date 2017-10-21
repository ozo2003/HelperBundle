<?php

namespace Sludio\HelperBundle\Translatable\Admin;

trait AdminTrait
{
    protected function getRedis()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        $redis = 'snc_redis.'.$kernel->getContainer()->getParameter('sludio_helper.redis.translation');

        return $kernel->getContainer()->get($redis);
    }

    public function postUpdate($object)
    {
        $key = strtolower($object->getClassName()).':translations:'.$object->getId();
        $this->getRedis()->del($key.':translations');
        $this->getRedis()->del($key.':checked');
    }

    public function getTranslationFilter($queryBuilder, $alias, $field, $value)
    {
        if (!isset($value['value'])) {
            return;
        }
        $queryBuilder->leftJoin('Sludio:Translation', 't', 'WITH', 't.foreignKey = '.$alias.'.id');
        $queryBuilder->andWhere("t.field = '$field'");
        $queryBuilder->andWhere("t.objectClass = '".$this->getClass()."'");
        $queryBuilder->andWhere("t.content LIKE '%".$value['value']."%'");
        $queryBuilder->setFirstResult(0);

        return true;
    }
}