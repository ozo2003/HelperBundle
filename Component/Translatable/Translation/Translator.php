<?php

namespace Sludio\HelperBundle\Component\Translatable\Translation;

use Lexik\Bundle\TranslationBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\HttpFoundation\Request;

class Translator extends BaseTranslator
{
    public function addDatabaseResources()
    {
        return parent::addDatabaseResources();
    }

    public function removeCacheFile($locale)
    {
        return parent::removeCacheFile($locale);
    }

    public function removeLocalesCacheFiles(array $locales)
    {
        return parent::removeLocalesCacheFiles($locales);
    }

    protected function invalidateSystemCacheForFile($path)
    {
        return parent::invalidateSystemCacheForFile($path);
    }

    public function getFormats()
    {
        return parent::getFormats();
    }

    public function getLoader($format)
    {
        return parent::getLoader($format);
    }

    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $request = $this->container->get('request');
        if ($request->get('print_debug') === 'echo_text') {
            return $domain.'.'.$id;
        }

        return parent::trans($id, $parameters, $domain, $locale);
    }
}
