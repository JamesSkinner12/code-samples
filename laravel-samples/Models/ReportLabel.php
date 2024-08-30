<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLabel extends Model
{
    protected $table = 'report_labels';
    protected $guarded = [];
    public $timestamps = true;
    protected $hidden = ['id', 'nillable', 'created_at', 'updated_at'];

    public function entries()
    {
        return $this->hasMany('\App\Models\ReportEntry', 'id', 'label_id');
    }
}
