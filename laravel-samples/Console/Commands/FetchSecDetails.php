<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 5/10/19
 * Time: 5:24 PM
 */

namespace App\Console\Commands;

use App\Repositories\CompanyRepository;
use Illuminate\Console\Command;
use App\Jobs\UpdateCompanySecDetails;

class FetchSecDetails extends Command
{
    protected $signature = 'company:sec-details';
    protected $description = "Fetch and set details for the provided company according to SEC data";


    public function handle(CompanyRepository $companies)
    {
        foreach ($companies->all() as $company) {
            UpdateCompanySecDetails::dispatch($company);
        }
    }
}