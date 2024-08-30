<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 4/10/19
 * Time: 8:25 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CompanySubscription extends Model
{
    public $table = 'user_company_subscriptions';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function company()
    {
        return $this->hasOne('\App\Models\Company', 'id', 'company_id');
    }
}
