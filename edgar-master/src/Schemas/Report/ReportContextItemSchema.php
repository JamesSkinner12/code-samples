<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/29/19
 * Time: 11:29 AM
 */

namespace Edgar\Schemas\Report;


use Schematics\Schematic;

class ReportContextItemSchema extends Schematic
{

    public function map()
    {
        return [
            'id' => '@id',
            'cik' => 'xbrli:entity|xbrli:identifier|#',
            'segment' => 'xbrli:entity|xbrli:segment',
            'period' => 'xbrli:period',
        ];
    }

    public function schemas()
    {
        return [
            'segment' => ContextEntitySchema::class,
        ];
    }
}
