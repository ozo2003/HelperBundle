<?php

namespace Sludio\HelperBundle\Script\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

class UsortExtension extends Twig_Extension
{
    private $param;
    private $order;

    protected $shortFunctions;

    public function __construct($container)
    {
        $this->shortFunctions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions');
    }

    public function getName()
    {
        return 'sludio_helper.twig.usort_extension';
    }

    public function cmpOrderBy($aVar, $bVar)
    {
        $aValue = $aVar->{'get'.ucfirst($this->param)}();
        $bValue = $bVar->{'get'.ucfirst($this->param)}();
        switch ($this->order) {
            case 'asc':
                return $aValue > $bValue;
            case 'desc':
                return $aValue < $bValue;
        }
    }

    public function getFilters()
    {
        $array = [
            new Twig_SimpleFilter('sludio_usort', [
                $this,
                'usortFunction',
            ]),
        ];

        $short_array = [
            new Twig_SimpleFilter('usort', [
                $this,
                'usortFunction',
            ]),
        ];

        if ($this->shortFunctions) {
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
        usort($objects, [
            __CLASS__,
            'cmpOrderBy',
        ]);

        return $objects;
    }
}
