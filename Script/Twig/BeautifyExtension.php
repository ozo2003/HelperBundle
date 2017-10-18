<?php

namespace Sludio\HelperBundle\Script\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

class BeautifyExtension extends Twig_Extension
{
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
        $array = [
            new Twig_SimpleFilter('sludio_beautify', [
                $this,
                'beautify',
            ]),
            new Twig_SimpleFilter('sludio_urldecode', [
                $this,
                'url_decode',
            ]),
            new Twig_SimpleFilter('sludio_parse', [
                $this,
                'parse',
            ]),
            new Twig_SimpleFilter('sludio_file_exists', [
                $this,
                'file_exists',
            ]),
            new Twig_SimpleFilter('sludio_html_entity_decode', [
                $this,
                'html_entity_decode',
            ]),
            new Twig_SimpleFilter('sludio_strip_descr', [
                $this,
                'strip_descr',
            ]),
        ];

        $short_array = [
            new Twig_SimpleFilter('beautify', [
                $this,
                'beautify',
            ]),
            new Twig_SimpleFilter('urldecode', [
                $this,
                'url_decode',
            ]),
            new Twig_SimpleFilter('parse', [
                $this,
                'parse',
            ]),
            new Twig_SimpleFilter('file_exists', [
                $this,
                'file_exists',
            ]),
            new Twig_SimpleFilter('html_entity_decode', [
                $this,
                'html_entity_decode',
            ]),
            new Twig_SimpleFilter('strip_descr', [
                $this,
                'strip_descr',
            ]),
        ];

        if ($this->shortFunctions) {
            return array_merge($array, $short_array);
        } else {
            return $array;
        }
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
