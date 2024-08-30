<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 4:19 PM
 */

namespace Edgar\Schemas\ReportList;


use Schematics\Schematic;

class CompanyInfoSchema extends Schematic
{

    public function map()
    {
        return [
            'former_names' => 'formerly-names|names',
            'addresses' => 'addresses|address',
            'sic' => 'assigned-sic',
            'sic_description' => 'assigned-sic-desc',
            'sic_href' => 'assigned-sic-href',
            'cik' => 'cik',
            'cik_href' => 'cik-href',
            'name' => 'conformed-name',
            'fiscal_year_end' => 'fiscal-year-end',
            'state_location' => 'state-location',
            'state_of_incorporation' => 'state-of-incorporation',
            'assistant_director' => 'assitant-director'
        ];
    }
}
