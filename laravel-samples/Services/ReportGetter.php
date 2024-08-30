<?php

namespace App\Services;

use App\Models\CompanyReport;
use App\Models\ReportEntry;
use Edgar\Report;
use App\Models\ReportLabel;
use App\Models\ContextItem;
use App\Models\ContextSegment;

ini_set('memory_limit', '1028M');

class ReportGetter
{
    protected $report;
    protected $handle;
    protected $collection;
    protected $labels;
    protected $contexts;
    protected $errors;


    public function __construct(CompanyReport $report)
    {
        $this->report = $report;
    }

    public function report()
    {
        return $this->report;
    }

    public function errors()
    {
        return $this->errors;
    }

    public function handle()
    {
        if (empty($this->handle)) {
            $this->handle = new Report($this->report->report_document->link);
        }
        return $this->handle;
    }

    public function collection()
    {
        if (empty($this->collection)) {
            $this->collection = $this->handle()->collection();
        }
        return $this->collection;
    }

    public function labelSummary()
    {
        if ($this->collectLabelSummaries()) {
            return $this->labels;
        }
        return false;
    }

    public function collectLabelSummaries()
    {
        foreach (array_keys($this->collection()) as $key) {
            try {
                if (empty($this->labels[$key])) {
                    $this->labels[$key] = $this->saveLabelSummary($this->handle()->meta()->findDetail($key));
                }
            } catch (\Exception $e) {
                $this->errors[$key][] = $e->getMessage();
            }
        }
        return true;
    }

    protected function trimRecord($records = [])
    {
        return array_map(function($record) {
            return trim($record);
        }, $records);
    }

    public function saveLabelSummary($values = [])
    {
        try {
            $record = [
                'search_id' => $values['id'],
                'name' => $values['name'],
                'nillable' => (!empty($values['nullable']) && $values['nullable'] === 'true') ? true : false,
                'type' => $values['type'],
                'balance' => $values['balance'],
                'period_type' => $values['period_type'],
                'substitution_group' => $values['substitution_group'],
                'label' => $values['meta']['value'],
                'description' => (!empty($values['meta']['description'])) ? $values['meta']['description'] : null,
                'readable_label' => (!empty($values['meta']['readable_label'])) ? $values['meta']['readable_label'] : null
            ];
            $record = $this->trimRecord($record);
            return ReportLabel::firstOrCreate($record);

        } catch (\Exception $e) {
            $this->errors[$values['id']][] = $e->getMessage();
            return false;
        }
    }

    public function collectContextItems()
    {
        foreach ($this->handle()->contextItems() as $contextItem) {
            try {
                $context = $this->handle()->findContextById($contextItem['id']);
                $this->contexts[$contextItem['id']] = ContextItem::firstOrCreate([
                    'search_id' => $context['id'],
                    'instant' => $context['period']['instant'],
                    'start_date' => $context['period']['start'],
                    'end_date' => $context['period']['end']
                ]);
                if (!empty($context['segment']['members'])) {
                    foreach ($context['segment']['members'] as $member) {
                        $dimension = $this->saveLabelSummary($member['dimension']);
                        $value = $this->saveLabelSummary($member['value']);
                        ContextSegment::firstOrCreate([
                            'context_id' => $this->contexts[$contextItem['id']]->id,
                            'dimension_id' => $dimension->id,
                            'value_id' => $value->id
                        ]);
                    }

                }
            } catch (\Exception $e) {
                $this->errors[$contextItem['id']][] = $e->getMessage();
            }
        }
    }

    public function collectEntries()
    {
        foreach ($this->collection() as $key => $values) {
            try {
                $labelID = ReportLabel::where('search_id', '=', $values['details']['id'])->first()->id;
                foreach ($values['items'] as $itm) {
                    ReportEntry::firstOrCreate([
                        'label_id' => $labelID,
                        'report_id' => $this->report->id,
                        'context_id' => (!empty($itm['context']['id'])) ? $this->contexts[$itm['context']['id']]->id : null,
                        'value' => $itm['value']
                    ]);
                }
            } catch (\Exception $e) {
                $this->errors[$key][] = $e->getMessage();
            }
        }
    }

    public function import()
    {
        $this->collectLabelSummaries();
        $this->collectContextItems();
        $this->collectEntries();
        $this->report()->update(['collected' => true]);
        return $this->errors;
    }
}
