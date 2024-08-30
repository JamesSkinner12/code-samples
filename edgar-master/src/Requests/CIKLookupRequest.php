<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 2:25 PM
 */

namespace Edgar\Requests;

use Edgar\Edgar;
use Edgar\Tools\Request;
use Edgar\Responses\CIKLookupResponse;

class CIKLookupRequest extends Request
{
    public function lookup($name)
    {
        $name = Edgar::suggestSearchName($name);
        $response = new CIKLookupResponse($this->request('cgi-bin/cik_lookup', 'POST', [
            'form_params' => [
                'company' => $name
            ]
        ]));
        foreach ($response->contents() as $item) {
            $output[] = [
                'cik' => $item['CIK Code'],
                'name' => $item['Company Name']
            ];
        }
        if (!empty($output)) {
            $mostValid = Edgar::mostValidItem($name, array_column($output, 'name'));
            return $output[array_search($mostValid, array_column($output, 'name'))];
        }
        return null;
    }
}