<?php

namespace Sludio\HelperBundle\Script\Twig;

use Symfony\Component\HttpFoundation\Request;
use Twig_Extension;
use Twig_SimpleFunction;

class BrowserExtension extends Twig_Extension
{
    protected $short_functions;

    public function __construct($container)
    {
        $this->short_functions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions', false);
    }

    public function getFunctions()
    {
        $array = array(
            new Twig_SimpleFunction('sludio_is_ie', array($this, 'isIE')),
        );

        $short_array = array(
            new Twig_SimpleFunction('is_ie', array($this, 'isIE')),
        );

        if ($this->short_functions) {
            return array_merge($array, $short_array);
        } else {
            return $array;
        }
    }

    public function getName()
    {
        return 'sludio_helper.twig.browser_extension';
    }

    public function isIE()
    {
        $request = Request::createFromGlobals();
        $ua = $request->server->get('HTTP_USER_AGENT');
        if (strpos($ua, 'MSIE') || strpos($ua, 'Edge') || strpos($ua, 'Trident/7')) {
            return 1;
        }

        return 0;
    }
}
