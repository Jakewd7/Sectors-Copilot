<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentStepLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'chat_message_id',
        'step_type',
        'tool_name',
        'endpoint',
        'status',
        'payload_data',
        'error_message',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'payload_data' => 'array',
            'duration_ms' => 'integer',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }
}