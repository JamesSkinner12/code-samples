<?php

namespace App\Services;

use App\Models\Realtime\Record;
use App\Models\Realtime\LegacyRecord;
use Illuminate\Support\Facades\DB;

class Realtime
{

    public static function legacyDateRange()
    {
        return LegacyRecord::selectRaw("MIN(dateTime) as min, MAX(dateTime) as max")->where('dateTime', '>', '2017-01-01 00:00:00')->first()->toArray();
    }

    public static function currentDateRange()
    {
        return Record::selectRaw("MIN(dateTime) as min, MAX(dateTime) as max")->where('dateTime', '>', '2017-01-01 00:00:00')->first()->toArray();
    }

    public static function isLegacyDate($date)
    {
        $range = self::legacyDateRange();
        return ($range['min'] < $date && $range['max'] > $date);
    }

    public static function classInstance($date)
    {
        return (self::isLegacyDate($date)) ? LegacyRecord::class : Record::class;
    }
}
