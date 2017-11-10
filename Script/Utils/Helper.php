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
        return preg_replace('~\s+~', '', lcfirst(ucwords(str_replace('_', ' ', $string))));
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

        $trim = trim($value);
        if ($trim === '' || $trim === "''") {
            $value = null;
        }
    }

    public static function getUniqueId($length = 20)
    {
        try {
            $output = bin2hex(random_bytes($length));
        } catch (\Exception $exception) {
            $output = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil((int)($length / strlen($x))))), 1, $length);
        }

        return $output;
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

    public static function newPKValidate($personCode)
    {
        $personCode = str_replace('-', '', $personCode);

        // @formatter:off
        $calculations = [1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        // @formatter:on

        $sum = 0;
        foreach($calculations as $key => $calculation){
            $sum += ($personCode[$key] * $calculation);
        }

        $remainder = $sum % 11;

        if (1 - $remainder < -1) {
            return $personCode[10] === (1 - $remainder + 11);
        }

        return $personCode[10] === (1 - $remainder);
    }

    public static function validatePersonCode($personCode = null)
    {
        if ($personCode !== null) {
            $personCode = str_replace('-', '', $personCode);

            if (strlen($personCode) !== 11) {
                return 'error_length';
            }
            if (preg_match('/^\d+$/', $personCode) === null) {
                return 'error_symbols';
            }
            if ((int)substr($personCode, 0, 2) < 32) {
                if (!self::validateDate($personCode)) {
                    return 'error_invalid';
                }
            }
            if ((int)substr($personCode, 0, 2) > 32 || ((int)substr($personCode, 0, 2) === 32 && !self::newPKValidate($personCode))) {
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
