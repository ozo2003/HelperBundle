<?php

namespace Sludio\HelperBundle\Translatable\Router;

use JMS\I18nRoutingBundle\Router\LocaleResolverInterface;
use Symfony\Component\HttpFoundation\Request;

class DefaultLocaleResolver implements LocaleResolverInterface
{
    private $cookieName;
    private $hostMap;

    public function __construct($cookieName, array $hostMap = [])
    {
        $this->cookieName = $cookieName;
        $this->hostMap = $hostMap;
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException
     */
    public function resolveLocale(Request $request, array $availableLocales)
    {
        if (!empty($this->hostMap) && isset($this->hostMap[$host = $request->getHost()])) {
            return $this->hostMap[$host];
        }

        $functions = [
            'returnByParameter',
            'returnByPreviousSession',
            'returnByCookie',
            'returnByLang',
        ];

        foreach ($functions as $function) {
            if ($result = $this->{$function}($request, $availableLocales)) {
                return $result;
            }
        }

        return null;
    }

    protected function returnByParameter(Request $request)
    {
        if ($request->query->has('hl')) {
            $hostLanguage = $request->query->get('hl');

            if (preg_match('#^[a-z]{2}(?:_[a-z]{2})?$#i', $hostLanguage)) {
                return $hostLanguage;
            }
        }
    }

    protected function returnByPreviousSession(Request $request)
    {
        if ($request->hasPreviousSession()) {
            $session = $request->getSession();
            if ($session->has('_locale')) {
                return $session->get('_locale');
            }
        }
    }

    protected function returnByCookie(Request $request)
    {
        if ($request->cookies->has($this->cookieName)) {
            $hostLanguage = $request->cookies->get($this->cookieName);

            if (preg_match('#^[a-z]{2}(?:_[a-z]{2})?$#i', $hostLanguage)) {
                return $hostLanguage;
            }
        }
    }

    protected function returnByLang(Request $request, array $availableLocales)
    {
        $languages = [];
        foreach ($request->getLanguages() as $language) {
            if (\strlen($language) !== 2) {
                $newLang = explode('_', $language, 2);
                $languages[] = reset($newLang);
            } else {
                $languages[] = $language;
            }
        }
        $languages = array_unique($languages);
        if (!empty($languages)) {
            foreach ($languages as $lang) {
                if (\in_array($lang, $availableLocales, true)) {
                    return $lang;
                }
            }
        }
    }
}
