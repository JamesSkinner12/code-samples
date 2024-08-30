<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 5:12 PM
 */

namespace Edgar\Schemas\Report;

use \Schematics\Schematic;

class ReportSchema extends Schematic
{

    public function map()
    {
        return [
            'contexts' => 'xbrli:context',
            'units' => 'xbrli:unit',
            'gaap' => '^us-gaap:*',
            'dei' => '^dei:*'
        ];
    }

    public function schemas()
    {
        return [
            'contexts' => ReportContextItemSchema::class,
        ];
    }
}