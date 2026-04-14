<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'location',
        'venue_id',
        'created_by',
        'start_time',
        'end_time',
        'capacity',
        'status',
        'is_sponsorship_open',
        'rejection_reason',
        'image',
    ];

    protected $appends = [
        'time_status',
        'average_rating',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_sponsorship_open' => 'boolean',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function getAverageRatingAttribute()
    {
        // Compute the average rating on the fly
        $avg = $this->ratings()->avg('rating');
        return $avg ? round($avg, 1) : 0;
    }

    // ─── Time-Based Status Logic ─────────────────────────────────────────────

    public function getTimeStatusAttribute()
    {
        $now = now();
        if ($now < $this->start_time) {
            return 'upcoming';
        } elseif ($now > $this->end_time) {
            return 'ended';
        } else {
            return 'live';
        }
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    public function scopeLive($query)
    {
        return $query->where('start_time', '<=', now())
                     ->where('end_time', '>=', now());
    }

    public function scopeEnded($query)
    {
        return $query->where('end_time', '<', now());
    }

    /**
     * All users (sponsors) attached to this event.
     * Pivot contains: tier, contribution_amount, timestamps.
     * Auto-ordered: diamond → gold → silver → bronze.
     *
     * Usage:
     *   $event->sponsors                                         // all
     *   $event->sponsors()->wherePivot('tier', 'gold')->get()    // gold only
     */
    public function sponsors()
    {
        return $this->belongsToMany(User::class, 'event_sponsor', 'event_id', 'sponsor_id')
                    ->using(EventSponsor::class)
                    ->withPivot(['tier', 'contribution_amount'])
                    ->withTimestamps()
                    ->orderByRaw("FIELD(event_sponsor.tier, 'diamond', 'gold', 'silver', 'bronze')");
    }

    /**
     * Sponsors eager-loaded with their profiles — use this for ticket branding.
     *
     * Usage:
     *   $sponsors = $event->sponsorsWithProfiles();
     *   foreach ($sponsors as $user) {
     *       $label = $user->pivot->tierLabel();
     *       $logo  = $user->profile->logo;
     *   }
     */
    public function sponsorsWithProfiles()
    {
        return $this->sponsors()->with('profile')->get();
    }

    /**
     * Fetch sponsors filtered by a specific tier.
     *
     * Usage: $event->sponsorsByTier('diamond')
     */
    public function sponsorsByTier(string $tier)
    {
        return $this->sponsors()->wherePivot('tier', $tier)->get();
    }
}
