<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventSponsor extends Pivot
{
    protected $table = 'event_sponsor';

    public $incrementing = true; // we have an id column

    protected $fillable = [
        'event_id',
        'sponsor_id',
        'tier',
        'contribution_amount',
    ];

    protected $casts = [
        'contribution_amount' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The user (sponsor) on this pivot row.
     */
    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    /**
     * Shortcut to the sponsor's unified profile.
     * Usage: $pivot->profile->logo
     */
    public function profile()
    {
        return $this->belongsTo(User::class, 'sponsor_id')
                    ->with('profile');
    }

    // ─── Ticket Branding ─────────────────────────────────────────────────────

    /**
     * Returns the branding display label for this pivot's tier.
     *
     * diamond → logo alongside event logo (caller renders <img>)
     * gold    → "Sponsored by"
     * silver  → "Supported by"
     * bronze  → "Special thanks to"
     */
    public function tierLabel(): string
    {
        return Profile::tierLabel($this->tier ?? null);
    }

    /**
     * Returns true when this sponsor's logo should appear next to the event logo.
     */
    public function isDiamondTier(): bool
    {
        return $this->tier === 'diamond';
    }
}
