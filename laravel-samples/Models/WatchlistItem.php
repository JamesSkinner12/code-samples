<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WatchlistItem extends Pivot
{
    protected $table = 'watchlist_items';
    protected $guarded = [];

    public function watchlist()
    {
        return $this->hasOne('\App\Models\Watchlist', 'id', 'watchlist_id');
    }

    public function company()
    {
        return $this->hasOne('\App\Models\Company', 'id', 'company_id');
    }
}
