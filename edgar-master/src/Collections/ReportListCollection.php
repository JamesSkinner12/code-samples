<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/28/19
 * Time: 3:51 PM
 */

namespace Edgar\Collections;

use Edgar\Requests\ReportListRequest;

class ReportListCollection
{
    protected $cik;
    protected $reports;

    public function __construct($cik)
    {
        $this->cik = $cik;
    }

    public function all($types = null)
    {
        $service = ReportListRequest::collect()->find($this->cik, 0, 100, $types);
        $output = $service->withAvailableDocuments();
        while ($service->hasNextPage()) {
            $service = $service->nextPage(true);
            $output['entries'] = array_merge_recursive($output['entries'], $service->withAvailableDocuments()['entries']);
        }
        return $output;
    }

    public function quarterly()
    {
        return $this->all('10-Q');
    }

    public function annually()
    {
        return $this->all('10-K');
    }
}