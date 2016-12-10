<?php

namespace Sludio\HelperBundle\Usort\Twig;

class UsortExtension extends \Twig_Extension
{
    private $param;
    private $order;
    
    public function getName()
    {
        return 'usort_extension';
    }
    
    public function cmpOrderBy($a, $b)
    {
        switch($this->order){
            case 'asc': return $a->{'get'.ucfirst($this->param)}() > $b->{'get'.ucfirst($this->param)}(); break;
            case 'desc': return $a->{'get'.ucfirst($this->param)}() < $b->{'get'.ucfirst($this->param)}(); break;
        }
    }
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('usort', array($this, 'usortFunction')),
        );
    }
    
    public function usortFunction($objects, $parameter, $order = 'asc')
    {
        $this->param = $parameter;
        $this->order = strtolower($order);
        
        if(is_object($objects)){
            $array = $objects->toArray();
        }
        usort($array, array(__CLASS__, 'cmpOrderBy'));

        return $array;
    }
}
