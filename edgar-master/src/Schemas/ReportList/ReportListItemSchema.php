<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 3:11 PM
 */

namespace Edgar\Schemas\ReportList;
use Schematics\Schematic;

class ReportListItemSchema extends Schematic
{

    public function map()
    {
        return [
            'type' => 'category|@term',
            'accession' => 'content|accession-nunber',
            'file_number' => 'content|file-number',
            'file_number_href' => 'content|file-number-href',
            'date' => 'content|filing-date',
            'filing_href' => 'content|filing-href',
            'film_number' => 'content|film-number',
            'name' => 'content|form-name',
            'size' => 'content|size',
            'link' => 'link|@href',
            'title' => 'title',
            'last_updated' => 'updated'
        ];
    }
}