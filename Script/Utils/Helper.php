<?php

namespace Sludio\HelperBundle\Script\Utils;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Helper
{
    // @formatter:off
    /**
     * Cyrillic mapping.
     *
     * @var array
     */
    protected static $cyrMap = array(
        'е', 'ё', 'ж', 'х', 'ц', 'ч', 'ш', 'щ', 'ю', 'я',
        'Е', 'Ё', 'Ж', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ю', 'Я',
        'а', 'б', 'в', 'г', 'д', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'ъ', 'ы', 'ь', 'э',
        'А', 'Б', 'В', 'Г', 'Д', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Ъ', 'Ы', 'Ь', 'Э'
    );

    /**
     * Latin mapping.
     *
     * @var array
     */
    protected static $latMap = array(
        'ye', 'ye', 'zh', 'kh', 'ts', 'ch', 'sh', 'shch', 'yu', 'ya',
        'Ye', 'Ye', 'Zh', 'Kh', 'Ts', 'Ch', 'Sh', 'Shch', 'Yu', 'Ya',
        'a', 'b', 'v', 'g', 'd', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'ʺ', 'y', '–', 'e',
        'A', 'B', 'V', 'G', 'D', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'ʺ', 'Y', '–', 'E'
    );
    // @formatter:on

    public static function toCamelCase($string, $upFirst = true)
    {
        if ($upFirst) {
            return preg_replace('~\s+~', '', lcfirst(ucwords(str_replace('_', ' ', $string))));
        }

        return preg_replace('~\s+~', '', ucwords(str_replace('_', ' ', $string)));
    }

    public static function fromCamelCase($string, $separator = '_')
    {
        return strtolower(preg_replace('/(?!^)[[:upper:]]+/', $separator.'$0', $string));
    }

    public static function isEmpty($variable)
    {
        $result = true;

        if (\is_array($variable) && \count($variable) > 0) {
            foreach ($variable as $value) {
                $result = $result && self::isEmpty($value);
            }
        } else {
            $result = empty($variable);
        }

        return $result;
    }

    public static function swap(&$foo, &$bar)
    {
        $tmp = $foo;
        $foo = $bar;
        $bar = $tmp;
    }

    public static function removeDuplicates(&$array)
    {
        $array = array_map('unserialize', array_unique(array_map('serialize', $array)));
    }

    public static function cleanText($text)
    {
        return html_entity_decode(self::oneSpace(str_replace(' ?', '', mb_convert_encoding(strip_tags($text), 'UTF-8', 'UTF-8'))));
    }

    public static function oneSpace($text)
    {
        return preg_replace('/\s+/S', ' ', $text);
    }

    /**
     * Transliterates cyrillic text to latin.
     *
     * @param  string $text cyrillic text
     *
     * @return string latin text
     */
    public static function translit2($text)
    {
        return str_replace(self::$cyrMap, self::$latMap, $text);
    }

    /**
     * Transliterates latin text to cyrillic.
     *
     * @param  string $text latin text
     *
     * @return string cyrillic text
     */
    public static function translit4($text)
    {
        return str_replace(self::$latMap, self::$cyrMap, $text);
    }

    public static function multiple(array $keys)
    {
        foreach ($keys as $key) {
            if (!\is_array($key)) {
                return false;
            }
        }

        return true;
    }

    public static function multiset(array $keys)
    {
        foreach ($keys as $key) {
            if ($key === null) {
                return false;
            }
        }

        return true;
    }

    public static function useHttps(Request $request)
    {
        $https = false;
        if ($request->server->has('HTTPS') && 'on' === $request->server->get('HTTPS')) {
            $https = true;
        } elseif ($request->server->has('SERVER_PORT') && 443 === (int)$request->server->get('SERVER_PORT')) {
            $https = true;
        } elseif ($request->server->has('HTTP_X_FORWARDED_SSL') && 'on' === $request->server->get('HTTP_X_FORWARDED_SSL')) {
            $https = true;
        } elseif ($request->server->has('HTTP_X_FORWARDED_PROTO') && 'https' === $request->server->get('HTTP_X_FORWARDED_PROTO')) {
            $https = true;
        }

        return $https;
    }

    public static function getSchema(Request $request)
    {
        if (self::useHttps($request)) {
            return 'https://';
        }

        return 'http://';
    }

    public static function initialize(array $arguments, ContainerAwareCommand $command)
    {
        list($params, $input, $output) = $arguments;
        /** @var InputInterface $input */
        /** @var OutputInterface $output */

        foreach ($params as $param => $check) {
            $command->{$param} = $input->getOption($param);
            if (!$command->{$param}) {
                $output->writeln("Please provide --$param parameter!");

                return false;
            }

            if (!$check($command->{$param})) {
                $output->writeln("Incorrect input in --$param parameter!");

                return false;
            }
        }

        return true;
    }
}
