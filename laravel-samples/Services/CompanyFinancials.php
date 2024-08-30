<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ReportLabel;
use App\Models\ReportEntry;

class CompanyFinancials
{
    protected $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function get(ReportLabel $label, $map = null)
    {
        $func = function ($item) {
            if ($item->context()->exists()) {
                return (!empty($item->context->end_date)) ? $item->context->end_date : $item->context->instant;
            }
            return null;
        };
        $data = ReportEntry::whereIn('report_id', $this->company->reports()->select('id')->get()->toArray())
            ->whereLabelId($label->id)
            ->get()
            ->withContextsAndLabels()
            ->sortBy($func)
            ->toArray();
        return (!empty($map)) ? array_map($map, $data) : $data;
    }

    public static function commonMeasures()
    {
        return [
            'us-gaap_Assets',
            'us-gaap_Liabilities',
            'us-gaap_OperatingExpenses',
            'us-gaap_GrossProfit'
        ];
    }

    public function getAllCommonMeasures()
    {
        return $this->collect(self::commonMeasures());
        $output = [];
        $func = function ($item) {
            return [
                'value' => (int)$item['value'],
                'date' => (!empty($item['context']['end_date'])) ? $item['context']['end_date'] : $item['context']['instant']
            ];
        };
        foreach (self::commonMeasures() as $measure) {
            $label = ReportLabel::whereSearchId($measure)->first();
            foreach ($this->get($label, $func) as $item) {
                if (empty($output[$measure]) || !in_array($item['date'],
                        array_column($output[$measure]['items'], 'date'))) {
                    $output[$measure]['items'][] = $item;
                }
            }
            $output[$measure]['label'] = $label->toArray();
        }
        return $output;
    }

    protected static function toType($string)
    {
        switch ($string) {
            case ($string === 'xbrli:monetaryItemType'):
                return 'Monetary';
                break;
            case ($string === 'xbrli:sharesItemType'):
                return 'Shares';
                break;
            default:
                return 'None';
                break;
        }
    }

    public function latestAttribute($searchID)
    {
        $data = $this->collect($searchID);
        $key = array_keys($data)[0];
        $items = $data[$key]['items'];
        return end($items);
    }

    public function collect($labels = [])
    {
        if (!is_array($labels)) {
            $labels = [$labels];
        }
        $func = function ($item) {
            return [
                'value' => (int)$item['value'],
                'date' => (!empty($item['context']['end_date'])) ? $item['context']['end_date'] : $item['context']['instant']
            ];
        };
        foreach ($labels as $label) {
            $label = ReportLabel::whereSearchId($label)->first();
            $measure = $label->label;
            foreach ($this->get($label, $func) as $item) {
                if (empty($output[$measure]) || !in_array($item['date'],
                        array_column($output[$measure]['items'], 'date'))) {
                    $output[$measure]['items'][] = $item;
                }
            }
            $label = $label->toArray();
            $label['type'] = self::toType($label['type']);
            $label['balance'] = ucfirst($label['balance']);
            $output[$measure]['label'] = $label;
        }
        return (!empty($output)) ? $output : [];
    }

    /**
     * Formula-Based Values
     */

    public function workingCapital()
    {
        $assets = $this->latestAttribute("us-gaap_AssetsCurrent")['value'];
        $liabilities = $this->latestAttribute("us-gaap_LiabilitiesCurrent")['value'];
        return $assets - $liabilities;
    }

    public function operatingMargin()
    {
        $operatingIncome = $this->latestAttribute("us-gaap_OperatingIncomeLoss")['value'];
        $netRevenue = $this->latestAttribute("us-gaap_SalesRevenueNet")['value'];
        return $operatingIncome / $netRevenue;
    }

    public function inventoryTurnover()
    {
        $costOfGoodsSold = $this->latestAttribute("us-gaap_CostOfGoodsAndServicesSold")['value'];
        $averageInventory = $this->latestAttribute("us-gaap_InventoryNet")['value'];
        return $costOfGoodsSold / $averageInventory;
    }
}
