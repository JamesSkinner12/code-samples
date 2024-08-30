<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/30/19
 * Time: 9:22 PM
 */

namespace Edgar\Schemas\Meta;

use Schematics\Schematic;

class GaapItemSchema extends Schematic
{

    public function map()
    {
        return [
            'id' => '@id',
            'name' => '@name',
            'nullable' => '@nillable',
            'type' => '@type',
            'balance' => '@xbrli:balance',
            'period_type' => '@xbrli:periodType',
            'substitution_group' => '@substitutionGroup'
        ];
    }

}
