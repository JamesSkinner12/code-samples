<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 7/4/19
 * Time: 5:13 PM
 */

namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;

class ContextItemCollection extends Collection
{
    public function segments()
    {
        foreach ($this->items as $item) {
            $output[] = $item->segments()->with(['value', 'dimension'])->get()->toArray();
        }
        return (!empty($output)) ? $output : [];
    }
}