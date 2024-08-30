<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/3/19
 * Time: 2:42 PM
 */

namespace App\Collections;


use Illuminate\Database\Eloquent\Collection;
use App\Models\ReportLabel;

class EntriesCollection extends Collection
{

    public function withLabels()
    {
        return $this->each(function($item) {
            $item->label = $item->label()->select(['type','balance', 'label', 'description'])->first()->toArray();
        });
    }

    public function labelsIn()
    {
        return ReportLabel::whereIn('id', $this->pluck('label_id'))->get();
    }

    public function withContextsAndLabels()
    {
        return $this->each(function($item) {
            $item->label = $item->label()->select(['type','balance', 'label', 'description'])->first()->toArray();
            $item->context = $item->context;
            $item->segments = $item->segments;
        });
    }
}
