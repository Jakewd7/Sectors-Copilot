<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiCache extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'api_caches';

    protected $fillable = [
        'provider',
        'cache_key',
        'endpoint',
        'request_params',
        'response_payload',
        'hit_count',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'request_params' => 'array',
            'response_payload' => 'array',
            'hit_count' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Scope untuk mengambil cache yang masih valid (belum expired)
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}