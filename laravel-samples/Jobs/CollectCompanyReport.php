<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\CompanyReport;
use App\Services\ReportGetter;

class CollectCompanyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $report;
    protected $getter;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CompanyReport $report)
    {
        $this->report = $report;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getter = new ReportGetter($this->report);
        $this->getter->import();
    }
}
