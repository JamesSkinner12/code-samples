<?php

namespace App\Services;
use App\Models\CompanyReport;
class EntryAnalysis
{
    protected $entries;

    public function __construct($entries = [])
    {
        if (is_array($entries)) {
            $entries = $this->buildFromArray($entries);
        }
        $this->entries = $entries->each(function($entry) { $entry->reference_time = $entry->reference_time; });
        $this->entries = array_reverse($this->orderByDate());
    }

    protected function buildFromArray($entries)
    {
        $first = reset($entries);
        $entry = reset($first);
        $entries = CompanyReport::find($entry['report_id'])->entries()->whereLabelId($entry['label_id'])->with(['label', 'context', 'segments'])->get();
        return $entries;
    }

    public function label()
    {
        if (!empty($this->entries)) {
            try {
                return $this->entries[0]['label'];
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    public function orderByDate()
    {
        $func = function($entry) {
            $context = $entry['context'];
            return (!empty($context['start_date'])) ? $context['start_date'] : $context['instant'];
        };
        $arr = $this->entries->toArray();
        usort($arr, $func);
        return $arr;
    }

    public function timeframes()
    {
        $timeframes = [];
        foreach ($this->entries as $entry) {
            $timeframes[] = (is_array($entry['reference_time'])) ? implode(" to ", $entry['reference_time']) : $entry['reference_time'];
        }
        $timeframes = array_unique($timeframes);
        foreach ($timeframes as &$timeframe) {
            $timeframe = explode(" to ", $timeframe);
            if (count($timeframe) === 2) {
                $timeframe = ['start' => $timeframe[0], 'end' => $timeframe[1]];
            }
        }
        return $timeframes;
    }

    public function entriesWithDate($date)
    {
        $entries = [];
        foreach ($this->entries as $entry) {
            $refDate = $entry['reference_time'];
            if (!is_array($refDate)) { $refDate = [$refDate]; }
            if ($refDate === $date) {
                $entries[] = $entry;
            }
        }
        return $entries;
    }

    public function analysisData()
    {
        foreach ($this->timeframes() as $timeframe) {
            $entries = $this->entriesWithDate($timeframe);
            foreach ($entries as $key => $entry) {
                $segment = usableSegment($entry['segments']);
                $data[$segment][implode(" to ", $timeframe)] = [
                    'value' => $entry['value'],
                    'readable_value' => EntryType::setType($entry['value'], $entry['label']['type']),
                    'segments' => $entry['segments'],
                    'timeframe' => $timeframe
                ];
            }
        }
        return (!empty($data)) ? $data : [];
    }

    public function segmentAnalysis()
    {
        $summary = [];
        foreach ($this->analysisData() as $segmentName => $data) {
            $dates = array_keys($data);
            foreach ($data as $entryDate => $entry) {
                $previousIdx = array_search($entryDate, $dates) - 1;
                if ($previousIdx > -1) {
                    $previousDate = $dates[$previousIdx];
                    if ($previousDate) {
                        if ($previousIdx === 0) {
                            $summary[] = "During the timespan of " . $previousDate . ", a value of " . $data[$previousDate]['readable_value'] . " was measured for " . $segmentName . ".";
                        }
                        $summary[] = "This value increased (decreased) to " . $entry['readable_value'] . " during the timespan of " . $entryDate . ", with a growth rate of " . growth($entry['value'], $data[$previousDate]['value']) . "%";
                    }
                }
            }
        }
        return implode(" ", $summary);
    }

    public static function analyze($entries)
    {
        $class = new EntryAnalysis($entries);
        return $class->segmentAnalysis();
    }
}
