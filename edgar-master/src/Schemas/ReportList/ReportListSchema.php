<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 3:22 PM
 */

namespace Edgar\Schemas\ReportList;


use Schematics\Schematic;

class ReportListSchema extends Schematic
{

    public function map()
    {
        return [
            'entries' => 'entry',
            'company_info' => 'company-info',
            'links' => 'link',
        ];
    }

    public function schemas()
    {
        return [
            'entries' => ReportListItemSchema::class,
            'company_info' => CompanyInfoSchema::class,
            'links' => ReportListLinksSchema::class,
        ];
    }
}