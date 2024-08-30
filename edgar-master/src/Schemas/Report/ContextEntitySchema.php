<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/29/19
 * Time: 3:02 PM
 */

namespace Edgar\Schemas\Report;


use Schematics\Schematic;

class ContextEntitySchema extends Schematic
{

    public function map()
    {
        return [
            'members' => 'xbrldi:explicitMember',
            'types' => 'xbrldi:typedMember'
        ];
    }

    public function schemas()
    {
        return [
            'members' => ContextMemberSchema::class,
        ];
    }

    public function methods()
    {

    }
}