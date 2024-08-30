<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Jobs\CollectCompanyReport;

class ImportCompanyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:import-reports {symbol}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all reports for a given company';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!Company::whereSymbol($this->argument('symbol'))->exists()) {
            return $this->warn("No company exists with the provided symbol");
        }
        $company = Company::whereSymbol($this->argument('symbol'))->first();
        foreach ($company->reports as $report) {
            CollectCompanyReport::dispatch($report);
        }
    }
}
