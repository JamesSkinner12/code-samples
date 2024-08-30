<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 4:38 PM
 */

namespace Edgar;

use Edgar\Requests\CIKLookupRequest;

class CIK
{
    public static function collect()
    {
        return new CIKLookupRequest();
    }

    public static function get($name)
    {
        return self::collect()->lookup($name);
    }
}