<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Jobs\FetchCompanyReports;

class FetchSecReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Launches jobs to collect SEC reports for all companies';

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
        foreach (Company::all() as $company) {
            FetchCompanyReports::dispatch($company);
        }
    }
}
