<?php

namespace Sludio\HelperBundle\Script\Twig;

class UsortExtension extends \Twig_Extension
{
    use TwigTrait;

    private $param;
    private $order;

    public function __construct($shortFunctions)
    {
        $this->shortFunctions = $shortFunctions;
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
        $input = [
            'usort' => 'usortFunction',
        ];

        return $this->makeArray($input);
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
