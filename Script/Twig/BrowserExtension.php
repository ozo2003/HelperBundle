<?php

namespace Sludio\HelperBundle\Script\Twig;

use Symfony\Component\HttpFoundation\Request;
use Twig_Extension;
use Twig_SimpleFunction;

class BrowserExtension extends Twig_Extension
{
    protected $shortFunctions;

    public function __construct($container)
    {
        $this->shortFunctions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions');
    }

    public function getFunctions()
    {
        $array = [
            new Twig_SimpleFunction('sludio_is_ie', [
                $this,
                'isIE',
            ]),
        ];

        $short_array = [
            new Twig_SimpleFunction('is_ie', [
                $this,
                'isIE',
            ]),
        ];

        if ($this->shortFunctions) {
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
        $agent = $request->server->get('HTTP_USER_AGENT');
        if (strpos($agent, 'MSIE') || strpos($agent, 'Edge') || strpos($agent, 'Trident/7')) {
            return 1;
        }

        return 0;
    }
}
