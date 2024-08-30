<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 4:56 PM
 */

namespace Edgar\Requests;

use Edgar\Responses\AvailableDocumentsResponse;
use Edgar\Tools\Request;

class AvailableDocumentsRequest extends Request
{
    public function get($url)
    {
        return (new AvailableDocumentsResponse(file_get_contents($url)))->contents();
    }
}