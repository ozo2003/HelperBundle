<?php

namespace Sludio\HelperBundle\Script\Component\Translation;

use Lexik\Bundle\TranslationBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\HttpFoundation\Request;

class Translator extends BaseTranslator
{
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        global $kernel;
        
        $request = Request::createFromGlobals();

        $shortFunctions = $kernel->getContainer()->getParameter('sludio_helper.script.short_functions');
        if (($request->get('debug') === 'text' && $shortFunctions === true) || $request->get('sludio_debug') === 'text') {
            return $domain.'.'.$id;
        }

        $locale = $locale ?: $request->cookies->get('hl');
        if (!$locale) {
            $locale = $request->get('_locale', $kernel->getContainer()->getParameter('sludio_helper.locale'));
        }
        $locale = strtolower($locale);

        return parent::trans($id, $parameters, $domain, $locale);
    }
}
