<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 7/1/19
 * Time: 6:03 PM
 */

namespace App\Presenters;

class Presenter
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public static function collect($data)
    {
        $class = get_called_class();
        return new $class($data);
    }
}