<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PasswordResetCode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'code',
        'attempts',
        'reset_token',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'attempts'   => 'integer',
        ];
    }

    /* ───────────────────── Constants ───────────────────── */

    /** Code lifetime in minutes */
    const TTL_MINUTES = 5;

    /** Max wrong-code attempts before lockout */
    const MAX_ATTEMPTS = 3;

    /** Minimum seconds between two send requests for the same email */
    const RATE_LIMIT_SECONDS = 60;

    /** Max send requests per email within the cooldown window (per hour) */
    const MAX_SENDS_PER_HOUR = 5;

    /* ───────────────────── Scopes ──────────────────────── */

    /**
     * Scope: only rows that haven't expired yet.
     */
    public function scopeValid($query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subMinutes(self::TTL_MINUTES));
    }

    /**
     * Scope: only rows that are still within the attempt limit.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('attempts', '<', self::MAX_ATTEMPTS);
    }

    /* ───────────────────── Helpers ─────────────────────── */

    /**
     * Check if this code record has expired.
     */
    public function isExpired(): bool
    {
        return $this->created_at->addMinutes(self::TTL_MINUTES)->isPast();
    }

    /**
     * Check if this code record is locked (too many attempts).
     */
    public function isLocked(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Remaining attempts before lockout.
     */
    public function remainingAttempts(): int
    {
        return max(0, self::MAX_ATTEMPTS - $this->attempts);
    }
}
