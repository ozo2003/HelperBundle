<?php

namespace Sludio\HelperBundle\Translatable\Admin;

use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

trait AdminTrait
{
    abstract public function getClass();

    public function getTranslationFilter(ProxyQuery $queryBuilder, $alias, $field, $value)
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
