<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'is_suspended'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_suspended' => 'boolean',
        ];
    }

    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class);
    }

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function telemetryLogs()
    {
        return $this->hasMany(TelemetryLog::class);
    }

    public function marketInsights()
    {
        return $this->hasMany(MarketInsight::class, 'author_id');
    }

    public function promptStarters()
    {
        return $this->hasMany(PromptStarter::class);
    }

    public function adminAuditLogs()
    {
        return $this->hasMany(AdminAuditLog::class, 'admin_id');
    }
}
