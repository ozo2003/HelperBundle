<?php

namespace Sludio\HelperBundle\Script\Utils;

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

    public static function toCamelCase($string)
    {
        return preg_replace('~\s+~', '', lcfirst(ucwords(strtr($string, '_', ' '))));
    }

    public static function fromCamelCase($string, $separator = '_')
    {
        return strtolower(preg_replace('/(?!^)[[:upper:]]+/', $separator.'$0', $string));
    }

    public static function isEmpty($variable)
    {
        $result = true;

        if (is_array($variable) && count($variable) > 0) {
            foreach ($variable as $value) {
                $result = $result && self::isEmpty($value);
            }
        } else {
            $result = empty($variable);
        }

        return $result;
    }

    public static function variable(&$value)
    {
        if ($value instanceof \DateTime) {
            $value = "'".addslashes(trim($value->format('Y-m-d H:i:s')))."'";
        } elseif (!is_numeric($value)) {
            $value = "'".addslashes(trim($value))."'";
        }

        if (trim($value) === '' || trim($value) === "''") {
            $value = null;
        }
    }

    public static function getUniqueId($length = 20)
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    public static function validateDate($date)
    {
        $date = str_replace('-', '', $date);
        $day = intval(substr($date, 0, 2));
        $month = intval(substr($date, 2, 2));

        if ($month < 0 || $month > 12) {
            return false;
        }
        // @formatter:off
        $months = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        // @formatter:on
        if (intval(substr($date, 4, 2)) % 4 === 0) {
            $months[1] = 29;
        }

        return $day > 0 && $day <= $months[$month - 1];
    }

    public static function newPKValidate($personCode)
    {
        $personCode = str_replace('-', '', $personCode);
        // @formatter:off
        $sum =
            (substr($personCode, 0, 1) * 1) +
            (substr($personCode, 1, 1) * 6) +
            (substr($personCode, 2, 1) * 3) +
            (substr($personCode, 3, 1) * 7) +
            (substr($personCode, 4, 1) * 9) +
            (substr($personCode, 5, 1) * 10) +
            (substr($personCode, 6, 1) * 5) +
            (substr($personCode, 7, 1) * 8) +
            (substr($personCode, 8, 1) * 4) +
            (substr($personCode, 9, 1) * 2);
        // @formatter:on

        $remainder = $sum % 11;

        if (1 - $remainder < -1) {
            return substr($personCode, 10, 1) == (1 - $remainder + 11);
        } else {
            return substr($personCode, 10, 1) == (1 - $remainder);
        }
    }

    public static function validatePersonCode($personCode = null)
    {
        if ($personCode !== null) {
            $personCode = str_replace('-', '', $personCode);

            if (strlen($personCode) !== 11) {
                return 'error_length';
            }
            if (preg_match("/^[0-9]+$/", $personCode) === null) {
                return 'error_symbols';
            }
            if (intval(substr($personCode, 0, 2)) < 32) {
                if (!self::validateDate($personCode)) {
                    return 'error_invalid';
                }
            }
            if (intval(substr($personCode, 0, 2)) > 32 || (intval(substr($personCode, 0, 2)) === 32 && !self::newPKValidate($personCode))) {
                return 'error_invalid';
            }

            return true;
        }

        return 'error_empty';
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
        return html_entity_decode(self::oneSpace(str_replace(' ?', '', mb_convert_encoding(strip_tags($text), "UTF-8", "UTF-8"))));
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
            if (!is_array($key)) {
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
}
