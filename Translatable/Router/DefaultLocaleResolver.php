<?php

namespace Sludio\HelperBundle\Translatable\Router;

use JMS\I18nRoutingBundle\Router\LocaleResolverInterface;
use Symfony\Component\HttpFoundation\Request;

class DefaultLocaleResolver implements LocaleResolverInterface
{
    use LocaleResolverTrait;
    
    private $cookieName;
    private $hostMap;

    public function __construct($cookieName, array $hostMap = [])
    {
        $this->cookieName = $cookieName;
        $this->hostMap = $hostMap;
    }
}
