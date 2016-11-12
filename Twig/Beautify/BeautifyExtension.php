<?php

namespace Sludio\HelperBundle\Twig\Beautify;

class BeautifyExtension extends \Twig_Extension
{
    public function __construct($request_stack, $em)
    {
        $this->request = $request_stack->getCurrentRequest();
        $this->em = $em;
    }

    public function getName()
    {
        return 'beautify';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('beautify', array($this, 'beautify')),
            new \Twig_SimpleFilter('urldecode', array($this, 'url_decode')),
            new \Twig_SimpleFilter('parse', array($this, 'parse')),
            new \Twig_SimpleFilter('file_exists', array($this, 'file_exists')),
            new \Twig_SimpleFilter('html_entity_decode', array($this, 'html_entity_decode')),
            new \Twig_SimpleFilter('strip_descr', array($this, 'strip_descr')),
        );
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

    public function strip_descr($body, $length = 300)
    {
        if (strlen($body) > $length) {
            $body = substr($body, 0, strpos($body, ' ', $length)).'...';
        }

        return $body;
    }
}
