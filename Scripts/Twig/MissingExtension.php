<?php

namespace Sludio\HelperBundle\Scripts\Twig;

class MissingExtension extends \Twig_Extension
{
    public function __construct($request_stack, $em)
    {
        $this->request = $request_stack->getCurrentRequest();
        $this->em = $em;
    }

    public function getName()
    {
        return 'sludio_helper.twig.missing_extension';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('sludio_objects', array($this, 'getObjects')),
            new \Twig_SimpleFilter('sludio_svg', array($this, 'getSvg')),
        );
    }

    public function getObjects($class, $by, $order = null, $one = false)
    {
        $by = is_array($by) ? $by : array($by);
        $order = is_array($order) ? $order : array($order);

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
