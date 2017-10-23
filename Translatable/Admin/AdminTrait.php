<?php

namespace Sludio\HelperBundle\Translatable\Admin;
use Sludio\HelperBundle\Translatable\Entity\BaseEntity;

trait AdminTrait
{
    public abstract function getClass();

    protected function getRedis()
    {
        global $kernel;

        if ('AppCache' === get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        $redis = 'snc_redis.'.$kernel->getContainer()->getParameter('sludio_helper.redis.translation');

        return $kernel->getContainer()->get($redis);
    }

    public function postUpdate($object)
    {
        /** @var $object BaseEntity */
        $key = strtolower($object->getClassName()).':translations:'.$object->getId();
        $this->getRedis()->del($key.':translations');
        $this->getRedis()->del($key.':checked');
    }

    public function getTranslationFilter($queryBuilder, $alias, $field, $value)
    {
        if (!isset($value['value'])) {
            return false;
        }
        $queryBuilder->leftJoin('Sludio:Translation', 't', 'WITH', 't.foreignKey = '.$alias.'.id');
        $queryBuilder->andWhere("t.field = '$field'");
        $queryBuilder->andWhere("t.objectClass = '".$this->getClass()."'");
        $queryBuilder->andWhere("t.content LIKE '%".$value['value']."%'");
        $queryBuilder->setFirstResult(0);

        return true;
    }
}