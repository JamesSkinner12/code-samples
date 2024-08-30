<?php

namespace Edgar\Loaders;

use Edgar\Report;

class ReportFieldLoader
{
    protected $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function keys()
    {
        return array_keys($this->report->data());
    }

    public static function collect($report)
    {
        return new self($report);
    }

    public function isMulti($data)
    {
        return (is_int(array_keys($data)[0]));
    }

    public function getContextMemberNames($set)
    {
        return [
            $set['dimension'],
            $set['value']
        ];
    }
    
    public function contextMembers()
    {
        $output = [];
        $members = array_filter(array_column($this->report->contextItems(), 'segment'), function ($item) {
            return (!empty($item['members']));
        });
        foreach ($members as $member) {
            $member = $member['members'];
            if ($this->isMulti($member)) {
                foreach ($member as $item) {
                    $output = array_merge($output, $this->getContextMemberNames($item));
                }
            } else {
                $output = array_merge($output, $this->getContextMemberNames($member));
            }
        }
        return array_values(array_unique($output));
    }

    public static function loadContextMembers(Report $report)
    {
        foreach (self::collect($report)->contextMembers() as $member) {
            $output[$member] = $report->findContextById($member);
        }
        return (!empty($output)) ? $output : [];
    }
}
