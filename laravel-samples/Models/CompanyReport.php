<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 5/12/19
 * Time: 8:07 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Collections\ReportsCollection;
class CompanyReport extends Model
{
    protected $table = 'company_reports';
    protected $guarded = [];
    protected $hidden = ['company_id', 'created_at', 'updated_at', 'deleted_at', 'collected'];

    public function documents()
    {
        return $this->hasMany('App\Models\ReportDocument', 'report_id', 'id');
    }

    public function company()
    {
        return $this->hasOne('\App\Models\Company', 'id', 'company_id');
    }

    public function getReportDocumentAttribute()
    {
        return $this->documents()->whereIn("type", ["XML", "EX-101.INS"])->first();
    }

    public function entries()
    {
        return $this->hasMany('\App\Models\ReportEntry', 'report_id', 'id');
    }

    public function labels()
    {
        return $this->hasManyThrough('\App\Models\ReportLabel', '\App\Models\ReportEntry', 'report_id', 'id', 'id', 'label_id');
    }

    public function getTimeFrameAttribute()
    {
        $label = ReportLabel::whereSearchId("dei_DocumentFiscalPeriodFocus")->first();
        return $this->entries()->whereLabelId($label->id)->first()->reference_time;
    }

    public function newCollection(array $models = [])
    {
        return new ReportsCollection($models);
    }

    public function contexts()
    {
        return $this->hasManyThrough('\App\Models\ContextItem', '\App\Models\ReportEntry', 'report_id', 'id', 'id', 'context_id');
    }

    public function getDistinctContextsAttribute()
    {
        return $this->contexts()->with(['segments'])->get()->unique();
    }

    public function getSegmentDimensionsAttribute()
    {
        $output = [];
        $entries = array_filter($this->entries()->with(['segments'])->get()->map(function($entry){
            return $entry->segments->map(function($segment) {
                return $segment->dimension;
            });
        })->toArray(), function($entry) { return (!empty($entry)); });
        foreach ($entries as $entry) {
            if (count($entry) !== count($entry, COUNT_RECURSIVE)) {
                 foreach ($entry as $item) { $output[] = $item; }
            } else {
                $output[] = $entry;
            }
        }
        return array_unique($output, SORT_REGULAR);
    }

    public function entriesInSegment(ReportLabel $dimension)
    {
        $output = [];
        $entries = array_filter($this->entries()->with(['segments'])->get()->toArray(), function($entry) {
            foreach ($entry['segments'] as $segment) {
                if ($segment['dimension']['search_id'] === $dimension->search_id) {
                    return true;
                }
            }
            return false;
        });
        return $entries;
    }
}
