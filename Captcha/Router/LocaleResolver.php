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
    private $defaultLocale;

    /**
     * @var Request
     */
    private $request;

    private $availableLocales;

    /**
     * @param String       $defaultLocale
     * @param RequestStack $requestStack
     * @param              $availableLocales
     */
    public function __construct($defaultLocale, RequestStack $requestStack, $availableLocales)
    {
        $this->defaultLocale = $defaultLocale;
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
