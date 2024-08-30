<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContextSegment extends Model
{
    protected $table = 'context_segments';
    protected $guarded = [];
    protected $hidden = [
        'id',
        'context_id',
        'dimension_id',
        'value_id',
        'created_at',
        'updated_at',
        'laravel_through_key'
    ];

    public function context()
    {
        return $this->belongsTo('\App\Models\ContextItem', 'context_id', 'id');
    }

    public function dimension()
    {
        return $this->hasOne('\App\Models\ReportLabel', 'id', 'dimension_id');
    }

    public function value()
    {
        return $this->hasOne('\App\Models\ReportLabel', 'id', 'value_id');
    }
}
