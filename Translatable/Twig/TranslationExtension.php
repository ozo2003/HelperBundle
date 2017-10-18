<?php

namespace Sludio\HelperBundle\Translatable\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

class TranslationExtension extends Twig_Extension
{
    protected $request;
    protected $defaultLocale;
    protected $shortFunctions;

    public function __construct($request_stack, $default, $container)
    {
        $this->request = $request_stack->getCurrentRequest();
        $this->defaultLocale = $default;
        $this->shortFunctions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions');
    }

    public function getName()
    {
        return 'sludio_helper.twig.translate_extension';
    }

    public function getFilters()
    {
        $array = [
            new Twig_SimpleFilter('sludio_var', [
                $this,
                'getVar',
            ]),
        ];

        $short_array = [
            new Twig_SimpleFilter('var', [
                $this,
                'getVar',
            ]),
        ];

        if ($this->shortFunctions) {
            return array_merge($array, $short_array);
        } else {
            return $array;
        }
    }

    public function getVar($type, $object, $original = false, $locale = null)
    {
        if ($object && is_object($object)) {
            $hl = $this->request ? $this->request->cookies->get('hl') : $this->defaultLocale;

            $new_locale = $locale;
            if (!$locale) {
                $new_locale = $this->request ? $this->request->get('_locale') : $hl;
            }

            $trans = $object->getVariableByLocale($type, $new_locale, $original);
            $class = get_class($object);
            $class = str_replace('Proxies\__CG__\\', '', $class);

            return $trans;
        } else {
            return $type;
        }
    }
}
