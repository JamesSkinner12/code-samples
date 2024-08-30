<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 4:53 PM
 */

namespace Edgar\Responses;


use Edgar\Tools\Response;
use Sunra\PhpSimple\HtmlDomParser;

class AvailableDocumentsResponse extends Response
{
    public function contents()
    {
        $output = [];
        $keys = [
            'sequence',
            'description',
            'link',
            'type',
            'size'
        ];
        foreach (HtmlDomParser::str_get_html($this->body)->find("table[summary=Data Files]") as $line) {
            foreach ($line->find("tr") as $tr) {
                $tmp = [];
                foreach ($tr->find("td") as $td) {
                    if (!$td->has_child()) {
                        $tmp[] = $td->plaintext;
                    } else {
                        $tmp[] = "https://sec.gov" . $td->first_child()->getAttribute('href');
                    }
                }
                if (!empty($tmp)) {
                    $output[] = array_combine($keys, $tmp);
                }

            }
        }
        return $output;
    }
}