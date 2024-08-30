<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    protected $table = 'watchlists';
    protected $guarded = [];

    public function user()
    {
        return $this->hasOne('\App\User', 'id', 'user_id');
    }

    public function items()
    {
        return $this->hasMany('\App\Models\WatchlistItem');
    }

    public function companies()
    {
        return $this->belongsToMany('\App\Models\Company', 'watchlist_items')->using('App\Models\WatchlistItem');
    }
}
