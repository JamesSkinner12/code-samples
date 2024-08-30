<?php

namespace App\Models\Realtime;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Record extends Model
{
    protected $table = 'revised_realtime_data';
    protected $connection = 'stock_data';
    protected $guarded = [];

    public static function columnNames()
    {
        $keys = array_keys(self::first()->toArray());
        $func = function($key) {
            $handle = str_replace("_", " ", $key);
            return Str::title($handle);
        };
        $contents = array_map($func, $keys);
        $overrides = self::columnNameOverrides();

        return array_values(array_merge(array_combine($contents, $contents), $overrides));
    }

    public static function columnNameOverrides()
    {
        return [
            'High52' => '52 Week High',
            'Low52' => '52 Week Low',
            'Pe' => 'Price/Earnings Ratio',
            'Eps' => 'Earnings Per Share',
            'Est Earnings' => 'Estimated Earnings',
            'Week52Low Date' => '52 Week Low Date',
            'Week52High Date' => '52 Week High Date'
        ];
    }
}
