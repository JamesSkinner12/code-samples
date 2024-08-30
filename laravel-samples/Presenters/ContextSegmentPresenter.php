<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 7/1/19
 * Time: 6:03 PM
 */

namespace App\Presenters;


class ContextSegmentPresenter extends Presenter
{
    protected function axisItem()
    {
        $axis = $this->data['dimension'];
        return [
            'search_id' => $axis['search_id'],
            'label' => str_replace(" [Axis]", "", $axis['label']),
            'description' => $axis['description'],
        ];
    }

    public function memberItem()
    {
        $member = $this->data['value'];
        return [
            'search_id' => $member['search_id'],
            'label' => str_replace(" [Member]", "", $member['label']),
            'description' => $member['description'],
        ];
    }

    public static function simplify($data)
    {
        $class = ContextSegmentPresenter::collect($data);
        return [
            'value' => $class->memberItem(),
            'dimension' => $class->axisItem()
        ];
    }
}