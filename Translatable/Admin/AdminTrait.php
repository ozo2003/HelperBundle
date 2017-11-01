<?php

namespace Sludio\HelperBundle\Translatable\Admin;
use Sludio\HelperBundle\Translatable\Entity\BaseEntity;

trait AdminTrait
{
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