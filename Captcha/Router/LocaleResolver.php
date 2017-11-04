<?php

namespace Sludio\HelperBundle\Captcha\Router;

use Symfony\Component\HttpFoundation\Request;

final class LocaleResolver
{
    /**
     * @var String
     */
    private $defaultLocale;

    /**
     * @var Boolean
     */
    private $useLocaleFromRequest;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param String  $defaultLocale
     * @param Boolean $useLocaleFromRequest
     */
    public function __construct($defaultLocale, $useLocaleFromRequest)
    {
        $this->defaultLocale = $defaultLocale;
        $this->useLocaleFromRequest = $useLocaleFromRequest;
        $this->request = Request::createFromGlobals();
    }

    /**
     * @return String The resolved locale key, depending on configuration
     */
    public function resolve()
    {
        return $this->useLocaleFromRequest ? $this->request->getLocale() : $this->defaultLocale;
    }
}
