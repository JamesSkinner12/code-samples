<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 7/1/19
 * Time: 5:48 PM
 */

namespace App\Services;

use App\Models\CompanyReport;
use App\Presenters\ContextSegmentPresenter;

class ReportBuilder
{
    protected $report;
    protected $entries;
    protected $groupedByDate;
    protected $groupedByLabel;

    public function __construct(CompanyReport $report)
    {
        $this->report = $report;
        $this->groupedByDate = false;
        $this->groupedByLabel = false;
        $this->entries = $this->report->entries->each(function ($entry) {
            $entry->reference_time = $entry->reference_time;
        })->withContextsAndLabels()->toArray();
    }

    public function entries()
    {
        return $this->entries;
    }

    public function simplifySegments()
    {
        foreach ($this->entries as &$entry) {
            if (!empty($entry['segments'])) {
                foreach ($entry['segments'] as &$segment) {
                    $segment = ContextSegmentPresenter::simplify($segment);
                }
            }
        }
    }

    public function groupByLabel()
    {
        if (!$this->groupedByLabel) {
            $this->entries = array_group_by($this->entries, function ($entry) {
                return $entry['label']['label'];
            });
            $this->groupedByLabel = true;
        }
    }

    public function groupByEntryDate()
    {
        if (!$this->groupedByLabel) {
            $this->groupByLabel();
        }
        if (!$this->groupedByDate) {
            foreach ($this->entries as $label => &$entries) {
                $this->entries[$label] = array_group_by($entries, function ($entry) {
                    if (!is_array($entry['reference_time'])) {
                        $entry['reference_time'] = ['start' => $entry['reference_time']];
                    }
                    return implode(" to ", $entry['reference_time']);
                });
            }
            $this->groupedByDate = true;
        }
    }

    public function cleanedItems()
    {
        if (!$this->groupedByDate) {
            $this->groupByEntryDate();
        }
        foreach ($this->entries as $label => &$entriesGroup) {
            $handleEntry = reset($entriesGroup);
            $handleEntry = reset($handleEntry);
            $this->entries[$label]['details'] = ['label' => $handleEntry['label']];
            foreach ($entriesGroup as $date => &$entries) {
                foreach ($entries as &$entry) {
                    if ($date !== 'details') {
                        unset($entry['label']);
                        unset($entry['context']);
                    }
                }
            }
        }
    }
}