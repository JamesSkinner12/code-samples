<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 5/1/19
 * Time: 11:41 AM
 */

namespace Edgar\Schemas\Meta;


use Schematics\Schematic;

class DeiDocItemSchema extends Schematic
{
    public function map()
    {
        return [
            'label' => '@xlink:label',
            'role' => '@xlink:role',
            'value' => '#',
            'type' => '@xlink:type',
        ];
    }
}