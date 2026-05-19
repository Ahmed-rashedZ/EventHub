<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistanceRequest extends Model
{
    protected $fillable = [
        'assistant_id',
        'event_id',
        'manager_id',
        'status',
        'message',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function assistant()
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }
}
