<?php

namespace Sludio\HelperBundle\Translatable\Twig;

class TranslationExtension extends \Twig_Extension
{
    protected $em;
    protected $request;
    protected $defaultLocale;
    protected $short_functions;

    public function __construct($em, $request_stack, $default, $container)
    {
        $this->em = $em;
        $this->request = $request_stack->getCurrentRequest();
        $this->defaultLocale = $default;
        $this->short_functions = $container->hasParameter('sludio_helper.scripts.short_functions') && $container->getParameter('sludio_helper.scripts.short_functions', false);
    }

    public function getName()
    {
        return 'sludio_helper.twig.translate_extension';
    }

    public function getFilters()
    {
        $array = array(
            new \Twig_SimpleFilter('sludio_var', array($this, 'getVar')),
        );

        $short_array = array(
            new \Twig_SimpleFilter('var', array($this, 'getVar')),
        );

        if ($this->short_functions) {
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
