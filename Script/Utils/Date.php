<?php

namespace Sludio\HelperBundle\Script\Utils;

class Date
{
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
}
