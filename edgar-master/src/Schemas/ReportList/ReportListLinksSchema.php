<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 3:31 PM
 */

namespace Edgar\Schemas\ReportList;


use Schematics\Schematic;

class ReportListLinksSchema extends Schematic
{

    public function map()
    {
        return [
            'href' => '@href',
            'rel' => '@rel',
            'type' => '@type'
        ];
    }
}