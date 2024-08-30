<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 4:41 PM
 */

namespace Edgar;


use Edgar\Collections\ReportListCollection;

class Reports
{
    public static function getByCIK($cik)
    {
        return new ReportListCollection($cik);
    }

    public static function getByName($companyName)
    {
        try {
            return self::getByCIK(CIK::get($companyName));
        } catch (\Exception $e) {
            return [];
        }
    }
}