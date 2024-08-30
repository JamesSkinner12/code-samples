<?php

namespace App\Models;

use App\Collections\ContextItemCollection;
use Illuminate\Database\Eloquent\Model;

class ContextItem extends Model
{
    protected $table = 'context_items';
    protected $guarded = [];
    protected $hidden = ['id', 'created_at', 'updated_at'];

    public function segments()
    {
        return $this->hasMany('\App\Models\ContextSegment', 'context_id');
    }

    public function dimension()
    {
        return $this->hasOne('ReportLabel', 'id', 'dimension_id');
    }

    public function value()
    {
        return $this->hasOne('ReportLabel', 'id', 'value_id');
    }

    public function newCollection(array $models = [])
    {
        return new ContextItemCollection($models);
    }
}
