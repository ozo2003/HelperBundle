<?php

namespace Sludio\HelperBundle\Scripts\Twig;

class UsortExtension extends \Twig_Extension
{
    private $param;
    private $order;

    protected $short_functions;

    public function __construct($container)
    {
        $this->short_functions = $container->hasParameter('sludio_helper.scripts.short_functions') && $container->getParameter('sludio_helper.scripts.short_functions', false);
    }

    public function getName()
    {
        return 'sludio_helper.twig.usort_extension';
    }

    public function cmpOrderBy($a, $b)
    {
        switch ($this->order) {
            case 'asc': return $a->{'get'.ucfirst($this->param)}() > $b->{'get'.ucfirst($this->param)}(); break;
            case 'desc': return $a->{'get'.ucfirst($this->param)}() < $b->{'get'.ucfirst($this->param)}(); break;
        }
    }

    public function getFilters()
    {
        $array = array(
            new \Twig_SimpleFilter('sludio_usort', array($this, 'usortFunction')),
        );

        $short_array = array(
            new \Twig_SimpleFilter('usort', array($this, 'usortFunction')),
        );

        if ($this->short_functions) {
            return array_merge($array, $short_array);
        } else {
            return $array;
        }
    }

    public function usortFunction($objects, $parameter, $order = 'asc')
    {
        $this->param = $parameter;
        $this->order = strtolower($order);

        if (is_object($objects)) {
            $objects = $objects->toArray();
        }
        usort($objects, array(__CLASS__, 'cmpOrderBy'));

        return $objects;
    }
}
