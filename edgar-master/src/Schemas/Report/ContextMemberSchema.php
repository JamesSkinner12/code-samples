<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/29/19
 * Time: 3:47 PM
 */

namespace Edgar\Schemas\Report;


use Schematics\Schematic;

class ContextMemberSchema extends Schematic
{

    public function map()
    {
        return [
            'dimension' => '@dimension',
            'value' => '#'
        ];
    }
}