<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/11/19
 * Time: 5:16 PM
 */

namespace App\Models;


use Edgar\Reports as Filings;
use Edgar\Reports;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\Realtime;

class Company extends Model
{
    use SoftDeletes;

    public $table = 'companies';
    protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $guarded = [];

    public function reports()
    {
        return $this->hasMany('\App\Models\CompanyReport', 'company_id', 'id');
    }

    public function entries()
    {
        return $this->hasManyThrough('\App\Models\ReportEntry', '\App\Models\CompanyReport', 'id', 'report_id');
    }

    /**
     * @return bool|string
     */
    public function getLogoAttribute()
    {
        if (Storage::disk('public')->exists("/logos/" . $this->symbol . "_logo.jpg")) {
            return Storage::disk('public')->url("logos/" . $this->symbol . "_logo.jpg");
        }
        return false;
    }

    public function getAvailableSectorsAttribute()
    {
        $data = DB::table('companies')->selectRaw("DISTINCT sector")->get();
        return $data->each(function ($item) {
            $item->selected = ($item->sector === $this->sector) ? true : false;
        })->toArray();
    }

    public function getQuarterlyReportsAttribute()
    {
        return Reports::getByCIK($this->cik)->quarterly();
    }

    public function getAnnualReportsAttribute()
    {
        return Reports::getByCIK($this->cik)->annually();
    }

    public function getAvailableIndustriesAttribute()
    {
        return DB::table('companies')->selectRaw("DISTINCT industry")->get()
            ->each(function ($item) {
                $item->selected = ($item->industry === $this->industry) ? true : false;
            })->toArray();
    }

    public function getSecDetailsAttribute()
    {
        if (!empty($this->cik)) {
            $handle = Filings::getByCIK($this->cik)->quarterly();
            return $handle;
        }
        return null;
    }

    public function getRealtimeIdAttribute()
    {
        return DB::connection('stock_data')->table('company_data')->select('id')->where('symbol', '=',
            $this->symbol)->first()->id;
    }

    public function getQuoteAttribute()
    {
        $class = Realtime::classInstance(date('Y-m-d'));
        return $class::where('stock_id', '=', $this->getRealtimeIdAttribute())->orderByDesc('id')->first();
    }

    public function subscribers()
    {
        return $this->hasMany('\App\Models\CompanySubscription');
    }

    public function getMarketSegmentsAttribute()
    {
    }
}
