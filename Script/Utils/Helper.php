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

    const RAND_BASIC = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const RAND_EXTENDED = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-=+;:,.?';

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

    public static function genRandomString($length = 20, $chars = self::RAND_BASIC)
    {
        return substr(str_shuffle(str_repeat($chars, (int)ceil((int)($length / \strlen($chars))))), 1, $length);
    }

    public static function getUniqueId($length = 20)
    {
        try {
            return bin2hex(random_bytes($length));
        } catch (\Exception $exception) {
            return self::genRandomString($length);
        }
    }

    public static function validatePersonCode($personCode)
    {
        $personCode = str_replace('-', '', $personCode);
        $result = true;

        if (\strlen($personCode) !== 11 || preg_match('/^\d+$/', $personCode) === null) {
            $result = false;
        } elseif (((int)substr($personCode, 0, 2) === 32 && !self::newPKValidate($personCode)) || !self::validateDate($personCode)) {
            $result = false;
        }

        return $result;
    }

    public static function validateDate($date)
    {
        $date = str_replace('-', '', $date);
        $day = (int)substr($date, 0, 2);
        $month = (int)substr($date, 2, 2);

        if ($month < 0 || $month > 12) {
            return false;
        }
        // @formatter:off
        $months = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        // @formatter:on
        if ((int)substr($date, 4, 2) % 4 === 0) {
            $months[1] = 29;
        }

        return $day > 0 && $day <= $months[$month - 1];
    }

    public static function validateDate2($date, $format = 'd-m-Y H:i:s')
    {
        $object = \DateTime::createFromFormat($format, $date);

        return $object && $object->format($format) === $date;
    }

    public static function excelDate($timestamp, $format = 'd-m-Y H:i:s')
    {
        $base = 25569;
        if ($timestamp >= $base) {
            $unix = ($timestamp - $base) * 86400;
            $date = gmdate($format, $unix);
            if (self::validateDate2($date, $format)) {
                return $date;
            }
        }

        return $timestamp;
    }

    public static function newPKValidate($personCode)
    {
        $personCode = str_replace('-', '', $personCode);

        // @formatter:off
        $calculations = [1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        // @formatter:on

        $sum = 0;
        foreach ($calculations as $key => $calculation) {
            $sum += ($personCode[$key] * $calculation);
        }

        $remainder = $sum % 11;

        if (1 - $remainder < -1) {
            return $personCode[10] === (1 - $remainder + 11);
        }

        return $personCode[10] === (1 - $remainder);
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
