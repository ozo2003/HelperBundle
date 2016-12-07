<?php

namespace Sludio\HelperBundle\Translatable\Twig;

class TranslationExtension extends \Twig_Extension
{
    protected $em;
    protected $request;
    protected $defaultLocale;

    public function __construct($em, $request_stack, $default)
    {
        $this->em = $em;
        $this->request = $request_stack->getCurrentRequest();
        $this->defaultLocale = $default;
    }

    public function getName()
    {
        return 'translate';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('var', array($this, 'getVar')),
        );
    }

    public function getVar($type, $object)
    {
        if ($object && is_object($object)) {
            $hl = $this->request ? $this->request->cookies->get('hl') : $this->defaultLocale;

            $new_locale = $this->request ? $this->request->get('_locale') : $hl;

            return $object->getVariableByLocale($type, $new_locale);
        } else {
            return $type;
        }
    }
}
