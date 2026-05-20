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
        'venue_id',
        'external_venue_name',
        'external_venue_location',
        'booking_proof_path',
        'ministry_document_path',
        'period',
        'booking_date',
        'start_time',
        'end_time',
        'capacity',
        'image',
        'status',
        'rejection_reason',
        'cancellation_reason',
        'cancellation_rejection_reason',
        'review_message',
        'review_fields',
        'review_status',
        'is_sponsorship_open',
        'is_tickets_open',
        'is_exhibition',
        'is_applications_open',
        'is_published',
        'created_by',
        'external_schedule',
        'internal_schedule',
        'agenda',
        'event_objective',
        'target_audience',
        'published_schedule',
        'is_exhibitor_registration_open',
    ];

    protected $appends = [
        'time_status',
        'average_rating',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_sponsorship_open' => 'boolean',
        'is_tickets_open' => 'boolean',
        'is_exhibition' => 'boolean',
        'is_applications_open' => 'boolean',
        'is_published' => 'boolean',
        'review_fields' => 'array',
        'external_schedule' => 'array',
        'internal_schedule' => 'array',
        'agenda' => 'array',
        'published_schedule' => 'array',
        'is_exhibitor_registration_open' => 'boolean',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

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
        // Use preloaded aggregate when available (zero queries)
        if (array_key_exists('ratings_avg_rating', $this->attributes)) {
            $avg = $this->attributes['ratings_avg_rating'];
            return $avg ? round((float) $avg, 1) : 0;
        }

        // Fallback: compute on the fly (only for single-event usage)
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
                    ->orderByRaw("CASE WHEN event_sponsor.tier IS NULL THEN 1 ELSE 0 END, FIELD(event_sponsor.tier, 'diamond', 'gold', 'silver', 'bronze')");
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

    // ─── Exhibition Relationships ──────────────────────────────────────────

    public function exhibitionApplications()
    {
        return $this->hasMany(ExhibitionApplication::class);
    }

    public function booths()
    {
        return $this->hasMany(ExhibitionBooth::class);
    }

    public function exhibitors()
    {
        return $this->hasMany(ExhibitionApplication::class)
                    ->where('status', 'accepted')
                    ->with(['company.profile', 'booth']);
    }

    /**
     * Check if the event is currently accepting exhibitor applications.
     * Based on manual toggle AND 30-day deadline before start_time.
     */
    public function canAcceptExhibitorApplications(): bool
    {
        if (!$this->is_exhibition || !$this->is_exhibitor_registration_open) {
            return false;
        }

        // Must be at least 30 days before start_time
        if (now()->diffInDays($this->start_time, false) < 30) {
            return false;
        }

        return true;
    }
}
