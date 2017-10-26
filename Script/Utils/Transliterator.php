<?php

namespace Sludio\HelperBundle\Script\Utils;

class Transliterator
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

    /**
     * Transliterates cyrillic text to latin.
     *
     * @param  string $text cyrillic text
     *
     * @return string latin text
     */
    public static function translit2($text)
    {
        return self::transliterate($text, true);
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
        return self::transliterate($text, false);
    }

    /**
     * Transliterates cyrillic text to latin and vice versa
     * depending on $direction parameter.
     *
     * @param  string $text      latin text
     * @param  bool   $direction if true transliterates cyrillic text to latin, if false latin to cyrillic
     *
     * @return string transliterated text
     */
    public static function transliterate($text, $direction = true)
    {
        $from = self::$cyrMap;
        $to = self::$latMap;

        if ($direction) {
            Helper::swap($from, $to);
        }

        return str_replace($from, $to, $text);
    }
}