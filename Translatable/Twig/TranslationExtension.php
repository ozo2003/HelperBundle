<?php

namespace Sludio\HelperBundle\Translatable\Twig;

use Sludio\HelperBundle\Script\Twig\TwigTrait;
use Symfony\Component\HttpFoundation\RequestStack;

class TranslationExtension extends \Twig_Extension
{
    use TwigTrait;

    protected $request;
    protected $defaultLocale;

    public function __construct(RequestStack $requestStack, $default, $shortFunctions)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->defaultLocale = $default;
        $this->shortFunctions = $shortFunctions;
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
        if ($object && \is_object($object)) {
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
