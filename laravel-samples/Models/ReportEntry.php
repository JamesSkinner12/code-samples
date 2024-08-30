<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Collections\EntriesCollection;

class ReportEntry extends Model
{
    protected $table = 'report_entries';
    protected $guarded = [];
    protected $hidden = ['context_id', 'created_at', 'updated_at', 'hidden_at'];

    public function context()
    {
        return $this->hasOne('\App\Models\ContextItem', 'id', 'context_id');
    }

    public function label()
    {
        return $this->hasOne('\App\Models\ReportLabel', 'id', 'label_id');
    }

    public function segments()
    {
        return $this->hasManyThrough('\App\Models\ContextSegment', '\App\Models\ContextItem', 'id', 'context_id', 'context_id', 'id')->with(['dimension', 'value']);
    }

    public function getReferenceTimeAttribute()
    {
        $context = $this->context()->first();
        if (!empty($context->instant)) {
            return $context->instant;
        } elseif (!empty($context->start_date)) {
            return [
                'start' => $context->start_date,
                'end' => $context->end_date
            ];
        }
        return null;
    }

    public function newCollection(array $models = [])
    {
        return new EntriesCollection($models);
    }

}
