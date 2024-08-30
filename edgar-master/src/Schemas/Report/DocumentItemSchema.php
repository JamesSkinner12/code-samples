<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/30/19
 * Time: 10:13 AM
 */

namespace Edgar\Schemas\Report;


use Schematics\Schematic;

class DocumentItemSchema extends Schematic
{
    public function map()
    {
        return [
            'context' => '@contextRef',
            'id' => '@id',
            'value' => '#',
            'decimals' => '@decimals',
            'unit' => '@unitRef'
        ];
    }
}