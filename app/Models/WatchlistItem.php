<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchlistItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'watchlist_id',
        'stock_ticker',
        'note',
        'added_at',
    ];

    protected function casts()
    {
        return [
            'added_at' => 'datetime',
        ];
    }

    public function watchlist()
    {
        return $this->belongsTo(Watchlist::class);
    }
}