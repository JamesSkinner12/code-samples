<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Company;

class UpdateCompanySecDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $company;

    /**
     * Create a new job instance.
     *
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function fetchBusinessDetail($value, $details)
    {
        $detail = $details[array_search('business', array_column($details, '@type'))];
        if (!empty($detail)) {
            return $detail[$value];
        }
        return null;
    }

    protected function fetchSecDetails()
    {
        $details = $this->company->sec_details['company_info'];
        if (!empty($details)) {
            return [
                'cik' => $details['cik'],
                'state_location' => $details['state_location'],
                'state_of_incorporation' => $details['state_of_incorporation'],
                'city_location' => $this->fetchBusinessDetail('city', $details['addresses']),
                'street_address' => $this->fetchBusinessDetail('street1', $details['addresses']),
                'zip_code' => $this->fetchBusinessDetail('zip', $details['addresses'])
            ];
        }
        return false;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($details = $this->fetchSecDetails()) {
            $this->company->update($details);
        }
    }
}
