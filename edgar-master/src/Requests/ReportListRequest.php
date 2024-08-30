<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 2:34 PM
 */

namespace Edgar\Requests;


use Edgar\Responses\ReportListResponse;
use Edgar\Tools\Request;

class ReportListRequest extends Request
{
    public function find($cik, $start = 0, $count = 100, $types = "")
    {
        $url = self::SEC_URL . "cgi-bin/browse-edgar?" . http_build_query([
                'action' => 'getcompany',
                'CIK' => $cik,
                'type' => $types,
                'dateb' => '',
                'owner' => '',
                'exclude' => '',
                'start' => $start,
                'count' => $count,
                'output' => 'atom'
            ]);
        return new ReportListResponse(file_get_contents($url));
    }
}