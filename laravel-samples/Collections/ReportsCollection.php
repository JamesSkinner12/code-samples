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

class ReportsCollection extends Collection
{
    public function withEntries()
    {
        foreach ($this->items as $item) {
            $output[$item->id] = $item->entries()->get()->toArray();
        }
        return (!empty($output)) ? $output : [];
    }

    public function latest()
    {
        return $this->sortByDesc(function($report) {
            return $report->filing_date;
        });
    }
}
