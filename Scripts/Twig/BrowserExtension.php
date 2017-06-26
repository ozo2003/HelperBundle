<?php

namespace Sludio\HelperBundle\Scripts\Twig;

use Symfony\Component\HttpFoundation\Request;

class BrowserExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('sludio_is_ie', array($this, 'isIE')),
        );
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