<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 5/12/19
 * Time: 8:11 PM
 */

namespace App\Jobs;

use App\Models\CompanyReport;
use Edgar\Reports;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Company;


class FetchCompanyReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function saveReport($data = [])
    {
        if (!CompanyReport::where('report_url', '=', $data['link'])->exists()) {
            $report = CompanyReport::create([
                'identifier' => str_random(20),
                'company_id' => $this->company->id,
                'type' => $data['type'],
                'accession' => $data['accession'],
                'file_number' => $data['file_number'],
                'filing_date' => $data['date'],
                'report_url' => $data['link'],
                'film_number' => $data['film_number'],
                'report_name' => $data['name'],
                'file_size' => $data['size']
            ]);
            foreach ($data['documents'] as $document) {
                $report->documents()->create([
                    'report_id' => $report->id,
                    'description' => $document['description'],
                    'link' => $document['link'],
                    'type' => $document['type'],
                    'size' => $document['size']
                ]);
            }
        }
    }

    public function importQuarterly()
    {
            $reports = array_filter(Reports::getByCIK($this->company->cik)->quarterly()['entries'], function ($itm) {
                return (!empty($itm['documents']));
            });
            foreach ($reports as $report) {
                $this->saveReport($report);
            }
            return true;
    }

    public function importAnnual()
    {
            $reports = array_filter(Reports::getByCIK($this->company->cik)->annually()['entries'], function ($itm) {
                return (!empty($itm['documents']));
            });
            foreach ($reports as $report) {
                $this->saveReport($report);
            }
            return true;
    }

    public function handle()
    {
       
        $this->importQuarterly();
        $this->importAnnual();
    }

}
