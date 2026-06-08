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
        'start_time',
        'end_time',
        'capacity',
        'image',
        'status',
        'is_sponsorship_open',
        'is_tickets_open',
        'is_exhibition',
        'is_applications_open',
        'is_published',
        'created_by',
        'event_objective',
        'target_audience',
        'company_category_slug',
        'is_exhibitor_registration_open',
    ];

    protected $appends = [
        'time_status',
        'average_rating',
        // Backward-compat: include old column names in JSON for frontend views
        'external_venue_name',
        'external_venue_location',
        'booking_proof_path',
        'booking_proof',
        'booking_date',
        'period',
        'ministry_document_path',
        'ministry_document',
        'ministry_approval_doc',
        'external_schedule',
        'internal_schedule',
        'agenda',
        'published_schedule',
        'rejection_reason',
        'review_message',
        'review_fields',
        'review_status',
        'cancellation_reason',
        'cancellation_rejection_reason',
        'objective',
        'location_link',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_sponsorship_open' => 'boolean',
        'is_tickets_open' => 'boolean',
        'is_exhibition' => 'boolean',
        'is_applications_open' => 'boolean',
        'is_published' => 'boolean',
        'is_exhibitor_registration_open' => 'boolean',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // ─── Core Relationships ────────────────────────────────────────────────────

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

    // ─── New Normalized Relationships ──────────────────────────────────────────

    /**
     * External venue info (only exists when venue_id is null).
     */
    public function externalVenue()
    {
        return $this->hasOne(EventExternalVenue::class);
    }

    /**
     * Schedule data: ministry doc, internal/external schedules, agenda, published schedule.
     */
    public function schedule()
    {
        return $this->hasOne(EventSchedule::class);
    }

    /**
     * Review/cancellation workflow data.
     */
    public function review()
    {
        return $this->hasOne(EventReview::class);
    }

    // ─── Computed Attributes ──────────────────────────────────────────────────

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

    // ─── Sponsorship Relationships ────────────────────────────────────────────

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

        // Must be at least 60 days before start_time
        if (now()->diffInDays($this->start_time, false) < 60) {
            return false;
        }

        return true;
    }

    public function matchesCompanyCategory(?string $companyCategorySlug): bool
    {
        if (!$this->is_exhibition || !$this->company_category_slug || !$companyCategorySlug) {
            return false;
        }

        return $this->company_category_slug === $companyCategorySlug;
    }

    public function scopeBrowsableByCompany($query, User $company)
    {
        $slug = $company->profile?->company_type_slug;

        return $query
            ->where('is_exhibition', true)
            ->where('status', 'approved')
            ->where('is_published', true)
            ->where('is_exhibitor_registration_open', true)
            ->where('start_time', '>', now())
            ->when($slug, fn ($q) => $q->where('company_category_slug', $slug), fn ($q) => $q->whereRaw('1 = 0'));
    }

    // ─── Backward-Compatible Accessors (Safety Net) ──────────────────────────
    // These ensure any code or Blade views referencing old columns still work
    // via transparent delegation to the child relationships.

    // ── External Venue Accessors ──
    public function getExternalVenueNameAttribute()
    {
        return $this->externalVenue?->venue_name;
    }
    public function getExternalVenueLocationAttribute()
    {
        return $this->externalVenue?->venue_location;
    }
    public function getBookingProofPathAttribute()
    {
        return $this->externalVenue?->booking_proof_path;
    }
    public function getBookingDateAttribute()
    {
        return $this->externalVenue?->booking_date;
    }
    public function getPeriodAttribute()
    {
        return $this->externalVenue?->period;
    }

    // ── Schedule Accessors ──
    public function getMinistryDocumentPathAttribute()
    {
        return $this->schedule?->ministry_document_path;
    }
    public function getExternalScheduleAttribute()
    {
        return $this->schedule?->external_schedule;
    }
    public function getInternalScheduleAttribute()
    {
        return $this->schedule?->internal_schedule;
    }
    public function getAgendaAttribute()
    {
        return $this->schedule?->agenda;
    }
    public function getPublishedScheduleAttribute()
    {
        return $this->schedule?->published_schedule;
    }

    // ── Review Accessors ──
    public function getRejectionReasonAttribute()
    {
        return $this->review?->rejection_reason;
    }
    public function getReviewMessageAttribute()
    {
        return $this->review?->review_message;
    }
    public function getReviewFieldsAttribute()
    {
        return $this->review?->review_fields;
    }
    public function getReviewStatusAttribute()
    {
        return $this->review?->review_status ?? 'none';
    }
    public function getCancellationReasonAttribute()
    {
        return $this->review?->cancellation_reason;
    }
    public function getCancellationRejectionReasonAttribute()
    {
        return $this->review?->cancellation_rejection_reason;
    }

    // ── Backward-compatible accessors for company/sponsor dashboards ──
    public function getBookingProofAttribute()
    {
        return $this->externalVenue?->booking_proof_path;
    }
    public function getMinistryApprovalDocAttribute()
    {
        return $this->schedule?->ministry_document_path;
    }
    public function getMinistryDocumentAttribute()
    {
        return $this->schedule?->ministry_document_path;
    }
    public function getObjectiveAttribute()
    {
        return $this->event_objective;
    }
    public function getLocationLinkAttribute()
    {
        return $this->externalVenue?->venue_location ?? $this->venue?->location;
    }
}
