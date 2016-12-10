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
            case 'asc': return $a->{'get'.ucfirst($this->param)}() < $b->{'get'.ucfirst($this->param)}(); break;
            case 'desc': return $a->{'get'.ucfirst($this->param)}() > $b->{'get'.ucfirst($this->param)}(); break;
        }
    }
    
    public function usortFunction($object, $parameter, $order = 'asc')
    {
        $this->param = $parameter;
        $this->order = strtolower($order);
        
        usort($object, array(__CLASS__, 'cmpOrderBy'));

        return $object;
    }
}
