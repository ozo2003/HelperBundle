<?php

namespace Sludio\HelperBundle\Script\Component\Translation;

use Lexik\Bundle\TranslationBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\HttpFoundation\Request;

class Translator extends BaseTranslator
{
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $request = Request::createFromGlobals();
        $locale = $locale ?: $request->cookies->get('hl');

        if (!$locale) {
            global $kernel;
            $locale = $request->get('_locale', $kernel->getContainer()->getParameter('sludio_helper.locale'));
        }

        $locale = strtolower($locale);

        if ($request->get('sludio_debug') === 'text') {
            return $domain.'.'.$id;
        }

        return parent::trans($id, $parameters, $domain, $locale);
    }
}
