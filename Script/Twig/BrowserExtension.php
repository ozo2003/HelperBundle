<?php

namespace Sludio\HelperBundle\Script\Twig;

use Symfony\Component\HttpFoundation\Request;

class BrowserExtension extends \Twig_Extension
{
    use TwigTrait;

    protected $shortFunctions;

    public function __construct($container)
    {
        $this->shortFunctions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions');
    }

    public function getFunctions()
    {
        $input = [
            'is_ie' => 'isIE',
        ];

        return $this->makeArray($input, 'function');
    }

    public function getName()
    {
        return 'sludio_helper.twig.browser_extension';
    }

    public function isIE()
    {
        $request = Request::createFromGlobals();
        $agent = $request->server->get('HTTP_USER_AGENT');
        if (strpos($agent, 'MSIE') || strpos($agent, 'Edge') || strpos($agent, 'Trident/7')) {
            return 1;
        }

        return 0;
    }
}
