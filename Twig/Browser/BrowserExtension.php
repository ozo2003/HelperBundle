<?php

namespace Sludio\HelperBundle\Twig\Browser;

use SunCat\MobileDetectBundle\DeviceDetector\MobileDetector;
use SunCat\MobileDetectBundle\Helper\DeviceView;

class BrowserExtension extends \Twig_Extension
{   
    private $mobileDetector;
    private $deviceView;
    private $redirectConf;
    
    public function __construct(MobileDetector $mobileDetector, DeviceView $deviceView, array $redirectConf)
    {
        $this->mobileDetector = $mobileDetector;
        $this->deviceView = $deviceView;
        $this->redirectConf = $redirectConf;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('is_ie', array($this, 'isIE')),
        );
    }
    
    public function getName()
    {
        return 'sludio.browser.twig.browser_extension';
    }
    
    public function isIE()
    {
        return $this->mobileDetector->isIE();
    }
}
