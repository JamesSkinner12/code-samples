<?php

namespace Edgar\Responses;

use Edgar\Tools\Response;
use Sunra\PhpSimple\HtmlDomParser;

class CIKLookupResponse extends Response
{

    protected function getHeaders($pre)
    {
        foreach (explode("  ", $pre->plaintext) as $headerValue) {
            if (!empty(trim($headerValue))) {
                $headers[] = trim($headerValue);
            }
        }
        return (!empty($headers)) ? $headers : [];
    }

    protected function getValues($headers, $pre)
    {
        $re = '/(\d{5,})/m';
        $subst = '|--|$1';
        $text = preg_replace($re, $subst, $pre->plaintext);
        $text = explode("|--|", $text);
        foreach ($text as $item) {
            if (!empty($item)) {
                $output[] = array_combine($headers, preg_split("/(\s{2,})/", trim($item)));
            }
        }
        return (!empty($output)) ? $output : [];
    }

    public function contents()
    {
        $headers = [];
        $output = [];
        foreach (HtmlDomParser::str_get_html($this->body)->find("td[width=80%]") as $td) {
            foreach ($td->find("pre") as $idx => $pre) {
                if ($idx === 0) {
                    $headers = $this->getHeaders($pre);
                }
                if ($idx === 1) {
                    $output = $this->getValues($headers, $pre);
                }
            }
        }
        return $output;
    }
}