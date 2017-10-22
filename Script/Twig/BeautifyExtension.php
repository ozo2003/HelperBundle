<?php

namespace Sludio\HelperBundle\Script\Twig;

class BeautifyExtension extends \Twig_Extension
{
    use TwigTrait;

    protected $request;
    protected $shortFunctions;

    public function __construct($requestStack, $container)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->shortFunctions = $container->hasParameter('sludio_helper.script.short_functions') && $container->getParameter('sludio_helper.script.short_functions');
    }

    public function getName()
    {
        return 'sludio_helper.twig.beautify_extension';
    }

    public function getFilters()
    {
        $input = [
            'beautify' => 'beautify',
            'urldecode' => 'url_decode',
            'parse' => 'parse',
            'file_exists' => 'file_exists',
            'html_entity_decode' => 'html_entity_decode',
            'strip_descr' => 'strip_descr',
        ];

        return $this->makeArray($input);
    }

    public function url_decode($string)
    {
        return urldecode($string);
    }

    public function parse($string)
    {
        $str = parse_url($string);

        $argv = [];
        if (isset($str['query'])) {
            $args = explode('&', $str['query']);

            foreach ($args as $arg) {
                $tmp = explode('=', $arg, 2);
                $argv[$tmp[0]] = $tmp[1];
            }
        }

        return $argv;
    }

    public function file_exists($file)
    {
        return file_exists(getcwd().$file);
    }

    public function beautify($string)
    {
        $explode = explode('/', strip_tags($string));
        $string = implode(' / ', $explode);

        return $string;
    }

    public function html_entity_decode($str)
    {
        $str = html_entity_decode($str);
        $str = preg_replace('#\R+#', '', $str);

        return $str;
    }

    public function strip_descr($body, $fallback = null, $type = null, $lengthIn = 300)
    {
        if ($type && $fallback) {
            $length = isset($fallback[$type]) ? $fallback[$type] : null;
        }
        if (!isset($length)) {
            $length = $lengthIn;
        }

        if (strlen($body) > $length) {
            $body = substr($body, 0, strpos($body, ' ', $length)).'...';
        }

        return $body;
    }
}
