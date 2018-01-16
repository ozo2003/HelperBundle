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

    /**
     * @param String       $defaultLocale
     * @param Boolean      $useLocaleFromRequest
     * @param RequestStack $requestStack
     */
    public function __construct($defaultLocale, $useLocaleFromRequest, RequestStack $requestStack)
    {
        $this->defaultLocale = $defaultLocale;
        $this->useLocaleFromRequest = $useLocaleFromRequest;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function resolve()
    {
        return $this->resolveLocale($this->request, ['lv','ru']);
    }
}
