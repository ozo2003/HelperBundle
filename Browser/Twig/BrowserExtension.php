<?php

namespace Sludio\HelperBundle\Browser\Twig;

use Symfony\Component\HttpFoundation\Request;

class BrowserExtension extends \Twig_Extension
{   
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('is_ie', array($this, 'isIE')),
        );
    }
    
    public function getName()
    {
        return 'sludio_browser.twig.browser_extension';
    }
    
    public function isIE(){
        $request = Request::createFromGlobals();
        $ua = $request->server->get('HTTP_USER_AGENT');
        if(strpos($ua, 'MSIE') || strpos($ua, 'Edge') || strpos($ua, 'Trident/7')){
            return 1;
        }
        return 0;
    }
}
