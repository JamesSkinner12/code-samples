<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 5/1/19
 * Time: 11:30 AM
 */

namespace Edgar\Schemas\Meta;


use Schematics\Schematic;

class DeiItemLocatorSchema extends Schematic
{

    public function map()
    {
        return [
            'href' => '@xlink:href',
            'label' => '@xlink:label',
        ];
    }
}