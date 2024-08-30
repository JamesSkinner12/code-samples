<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 5/12/19
 * Time: 8:07 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ReportDocument extends Model
{
    protected $table = 'report_documents';
    protected $guarded = [];

    public function report()
    {
        return $this->belongsTo('App\Models\CompanyReport', 'report_id', 'id');
    }
}