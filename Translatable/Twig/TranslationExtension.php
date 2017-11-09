<?php

namespace Sludio\HelperBundle\Translatable\Twig;

use Sludio\HelperBundle\Script\Twig\TwigTrait;

class TranslationExtension extends \Twig_Extension
{
    use TwigTrait;

    protected $request;
    protected $defaultLocale;

    public function __construct($requestStack, $default, $shortFunctions)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->defaultLocale = $default;
        $this->shortFunctions = $shortFunctions;
    }

    public function getName()
    {
        return 'sludio_helper.twig.translate_extension';
    }

    public function getFilters()
    {
        $input = [
            'var' => 'getVar',
        ];

        return $this->makeArray($input);
    }

    public function getVar($type, $object, $original = false, $locale = null)
    {
        if ($object && is_object($object)) {
            $lang = $this->request ? $this->request->cookies->get('hl') : $this->defaultLocale;

            $new_locale = $locale;
            if (!$locale) {
                $new_locale = $this->request ? $this->request->get('_locale') : $lang;
            }

            return $object->getVariableByLocale($type, $new_locale, $original);
        }

        return $type;
    }
}
