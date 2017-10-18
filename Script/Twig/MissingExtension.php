<?php

namespace Sludio\HelperBundle\Script\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

class MissingExtension extends Twig_Extension
{
    protected $request;
    protected $entityManager;
    protected $shortFunctions;

    public function __construct($request_stack, $entityManager, $container)
    {
        $this->request = $request_stack->getCurrentRequest();
        $this->entityManager = $entityManager;

        $this->shortFunctions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions');
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

        if ($this->shortFunctions) {
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
            $objects = $this->entityManager->getRepository($class)->findOneBy($by, $order);
        } else {
            $objects = $this->entityManager->getRepository($class)->findBy($by, $order);
        }

        return $objects;
    }

    public function getSvg($svg)
    {
        return file_get_contents(getcwd().$svg);
    }
}
