<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/29/19
 * Time: 3:02 PM
 */

namespace Edgar\Schemas\Report;


use Schematics\Schematic;

class ContextPeriodSchema extends Schematic
{

    public function map()
    {
        return [
            'instant' => 'xbrli:instant',
            'start' => 'xbrli:startDate',
            'end' => 'xbrli:endDate'
        ];
    }
}
