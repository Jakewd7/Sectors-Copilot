<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'admin_id',
        'action',
        'target_table',
        'target_id',
        'before_payload',
        'after_payload',
        'ip_address',
        'user_agent',
    ];

    protected function casts()
    {
        return [
            'before_payload' => 'array',
            'after_payload' => 'array',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}