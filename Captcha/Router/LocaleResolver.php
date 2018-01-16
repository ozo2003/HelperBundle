<?php

namespace Sludio\HelperBundle\Captcha\Router;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Sludio\HelperBundle\Translatable\Router\LocaleResolverTrait;

final class LocaleResolver
{
    use LocaleResolverTrait;

    /**
     * @var String
     */
    protected $defaultLocale;

    /**
     * @var Boolean
     */
    protected $useLocaleFromRequest;

    /**
     * @var Request
     */
    protected $request;

    protected $availableLocales;

    /**
     * @param String       $defaultLocale
     * @param Boolean      $useLocaleFromRequest
     * @param RequestStack $requestStack
     */
    public function __construct($defaultLocale, $useLocaleFromRequest, RequestStack $requestStack, $availableLocales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->useLocaleFromRequest = $useLocaleFromRequest;
        $this->request = $requestStack->getCurrentRequest();
        $this->availableLocales = $availableLocales;
    }

    public function resolve()
    {
        $locale = $this->resolveLocale($this->request, $this->availableLocales);

        if (\in_array($locale, $this->availableLocales, true)) {
            return $locale;
        }

        return $this->defaultLocale;
    }
}
