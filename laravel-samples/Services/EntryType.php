<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 7/3/19
 * Time: 6:41 PM
 */

namespace App\Services;

use Carbon\Carbon;

class EntryType
{

    const types = [
        'dei:centralIndexKeyItemType',
        'dei:filerCategoryItemType',
        'dei:fiscalPeriodItemType',
        'dei:submissionTypeItemType',
        'dei:yesNoItemType',
        'nonnum:domainItemType',
        'nonnum:textBlockItemType',
        'num:percentItemType',
        'num:perShareItemType',
        'us-types:domainItemType',
        'us-types:perShareItemType',
        'us-types:textBlockItemType',
        'xbrli:booleanItemType',
        'xbrli:dateItemType',
        'xbrli:decimalItemType',
        'xbrli:durationItemType',
        'xbrli:gMonthDayItemType',
        'xbrli:gYearItemType',
        'xbrli:gYearMonthItemType',
        'xbrli:integerItemType',
        'xbrli:monetaryItemType',
        'xbrli:normalizedStringItemType',
        'xbrli:pureItemType',
        'xbrli:sharesItemType',
        'xbrli:stringItemType'
    ];


    public static function validType($type)
    {
        return (in_array($type, self::types));
    }

    /**
     * @param $input
     * @param $type
     * @return mixed
     * @throws \Exception
     */
    public static function setType($input, $type)
    {
        if (self::validType($type)) {
            $methodName = explode(":", $type)[1];
            return self::{$methodName}($input);
        }
        throw new \Exception("The type provided does not match any in the system");
    }

    public static function centralIndexKeyItemType($input)
    {
        return $input;
    }

    public static function filerCategoryItemType($input)
    {
        return $input;
    }

    public static function fiscalPeriodItemType($input)
    {
        return $input;
    }

    public static function submissionTypeItemType($input)
    {
        return $input;
    }

    public static function yesNoItemType($input)
    {
        return $input;
    }

    public static function domainItemType($input)
    {
        return $input;
    }

    public static function textBlockItemType($input)
    {
        return $input;
    }

    public static function percentItemType($input)
    {
        return $input . "%";
    }

    public static function perShareItemType($input)
    {
        return $input . "/share";
    }

    public static function booleanItemType($input)
    {
        return ($input === true) ? "True" : "False";
    }

    public static function dateItemType($input)
    {
        return $input;
    }

    public static function decimalItemType($input)
    {
        return number_format($input, 2);
    }

    public static function durationItemType($input)
    {
        return $input;
    }

    public static function gMonthDayItemType($input)
    {
        return $input;
    }

    public static function gYearItemType($input)
    {
        return $input;
    }

    public static function gYearMonthItemType($input)
    {
        $handle = Carbon::createFromFormat('Y-m', $input);
        return $handle->format('MMM Do YY');
    }

    public static function integerItemType($input)
    {
        return (int)$input;
    }

    public static function monetaryItemType($input)
    {
        $input = (int) $input;
        setlocale(LC_MONETARY, 'en_US.UTF-8');
        return money_format("%.2n", $input);
    }

    public static function normalizedStringItemType($input)
    {
        return (string)$input;
    }

    public static function pureItemType($input)
    {
        return (int)$input;
    }

    /**
     * For types of xbrli:sharesItemType
     *
     * @param $input
     * @return string
     */
    public static function sharesItemType($input)
    {
        return number_format(self::pureItemType($input));
    }

    /**
     * For types of xbrli:stringItemType
     *
     * @param $input
     * @return string
     */
    public static function stringItemType($input)
    {
        return (string)$input;
    }
}
