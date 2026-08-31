<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'chat_session_id',
        'role',
        'content',
        'structured_payload',
    ];

    protected function casts()
    {
        return [
            'structured_payload' => 'array',
        ];
    }

    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    public function stepLogs()
    {
        return $this->hasMany(AgentStepLog::class, 'chat_message_id');
    }
}