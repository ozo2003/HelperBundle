<?php

namespace Sludio\HelperBundle\Captcha\Router;

use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var RequestStack
     */
    private $requestStack;


    /**
     * @param String       $defaultLocale
     * @param Boolean      $useLocaleFromRequest
     * @param RequestStack $requestStack
     */
    public function __construct($defaultLocale, $useLocaleFromRequest, RequestStack $requestStack)
    {
        $this->defaultLocale        = $defaultLocale;
        $this->useLocaleFromRequest = $useLocaleFromRequest;
        $this->requestStack         = $requestStack;
    }

    /**
     * @return String The resolved locale key, depending on configuration
     */
    public function resolve()
    {
        return $this->useLocaleFromRequest
            ? $this->requestStack->getCurrentRequest()->getLocale()
            : $this->defaultLocale
        ;
    }
}