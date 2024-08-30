<?php

namespace App\Models\Realtime;

use Illuminate\Database\Eloquent\Model;
class LegacyRecord extends Model
{
    protected $table = 'realtime_data';
    protected $connection = 'stock_data';
    protected $guarded = [];
}
