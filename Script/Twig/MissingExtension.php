<?php

namespace Sludio\HelperBundle\Script\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

class MissingExtension extends Twig_Extension
{
    protected $request;
    protected $em;
    protected $short_functions;

    public function __construct($request_stack, $em, $container)
    {
        $this->request = $request_stack->getCurrentRequest();
        $this->em = $em;

        $this->short_functions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions', false);
    }

    public function getName()
    {
        return 'sludio_helper.twig.missing_extension';
    }

    public function getFilters()
    {
        $array = [
            new Twig_SimpleFilter('sludio_objects', [
                $this,
                'getObjects',
            ]),
            new Twig_SimpleFilter('sludio_svg', [
                $this,
                'getSvg',
            ]),
        ];

        $short_array = [
            new Twig_SimpleFilter('objects', [
                $this,
                'getObjects',
            ]),
            new Twig_SimpleFilter('svg', [
                $this,
                'getSvg',
            ]),
        ];

        if ($this->short_functions) {
            return array_merge($array, $short_array);
        } else {
            return $array;
        }
    }

    public function getObjects($class, $by, $order = null, $one = false)
    {
        $by = is_array($by) ? $by : [$by];
        $order = is_array($order) ? $order : [$order];

        if ($one) {
            $objects = $this->em->getRepository($class)->findOneBy($by, $order);
        } else {
            $objects = $this->em->getRepository($class)->findBy($by, $order);
        }

        return $objects;
    }

    public function getSvg($svg)
    {
        return file_get_contents(getcwd().$svg);
    }
}
