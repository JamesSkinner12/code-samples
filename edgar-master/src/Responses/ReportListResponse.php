<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 2:34 PM
 */

namespace Edgar\Responses;


use Edgar\Requests\AvailableDocumentsRequest;
use Edgar\Schemas\ReportList\ReportListSchema;
use Edgar\Tools\Response;

class ReportListResponse extends Response
{
    public function summary()
    {
        return ReportListSchema::serialize($this->contents());
    }

    public function withAvailableDocuments()
    {
        $details = $this->summary();
        if (!is_int(array_keys($details['entries'])[0])) {
            $entries = $details['entries'];
            unset($details['entries']);
            $details['entries'][] = $entries;
        }
        foreach ($details['entries'] as &$detail) {
            $detail['documents'] = (new AvailableDocumentsRequest())->get($detail['filing_href']);
        }
        return $details;
    }

    public function companyInfo()
    {
        return $this->summary()['company_info'];
    }

    public function links()
    {
        return $this->summary()['links'];
    }

    public function count()
    {
        return count($this->contents()['entry']);
    }

    public function hasNextPage()
    {
        return ($this->nextPage() !== null);
    }

    public function nextPage($get = false)
    {
        foreach ($this->links() as $link) {
            if ($link['rel'] === "next") {
                return ($get) ? new ReportListResponse(file_get_contents($link['href'])) : $link;
            }
        }
        return null;
    }
}
