<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

use App\Models\Profile;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'contact_email',
        'password',
        'role',
        'is_active',
        'event_id',
        'avatar',
        'image',
        'phone',
        'bio',
        'social_links',
        'verification_status',
        'verification_notes',
        'fcm_token',
        'interests',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Backward-compat: include old doc_* column names in JSON for frontend views
    protected $appends = [
        'doc_commercial_register',
        'doc_tax_number',
        'doc_articles_of_association',
        'doc_practice_license',
        'doc_commercial_register_status',
        'doc_tax_number_status',
        'doc_articles_of_association_status',
        'doc_practice_license_status',
        'doc_commercial_register_note',
        'doc_tax_number_note',
        'doc_articles_of_association_note',
        'doc_practice_license_note',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'social_links' => 'array',
            'interests' => 'array',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The unified profile for this user (exists for ALL roles).
     * Sponsors populate company fields; event managers use individual fields.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Verification documents for this user (partners only).
     * Each row = one document type (commercial_register, tax_number, etc.)
     */
    public function documents()
    {
        return $this->hasMany(UserDocument::class);
    }

    /**
     * All events created/managed by this user (event_manager role).
     */
    public function managedEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * All events this user is sponsoring (sponsor role).
     * Includes pivot: tier, contribution_amount.
     */
    public function sponsoredEvents()
    {
        return $this->belongsToMany(Event::class, 'event_sponsor', 'sponsor_id', 'event_id')
                    ->using(EventSponsor::class)
                    ->withPivot(['tier', 'contribution_amount'])
                    ->withTimestamps();
    }

    /**
     * All attendance logs for this assistant (scanned_by).
     */
    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'scanned_by');
    }

    /**
     * All assistance requests received by this assistant.
     */
    public function assistanceRequests()
    {
        return $this->hasMany(AssistanceRequest::class, 'assistant_id');
    }

    /**
     * All exhibition applications by this company.
     */
    public function exhibitionApplications()
    {
        return $this->hasMany(ExhibitionApplication::class, 'company_id');
    }

    /**
     * All booths assigned to this company.
     */
    public function assignedBooths()
    {
        return $this->hasMany(ExhibitionBooth::class, 'company_id');
    }

    /**
     * Events this assistant has accepted invitations for.
     */
    public function assistedEvents()
    {
        return $this->belongsToMany(Event::class, 'assistance_requests', 'assistant_id', 'event_id')
                    ->wherePivot('status', 'accepted')
                    ->withPivot(['status', 'responded_at', 'message'])
                    ->withTimestamps();
    }

    /**
     * Check if this assistant has access to a specific event
     * (either via new invitation system or old direct event_id assignment).
     */
    public function hasAccessToEvent(int $eventId): bool
    {
        // New system: accepted assistance request
        $hasRequest = AssistanceRequest::where('assistant_id', $this->id)
            ->where('event_id', $eventId)
            ->where('status', 'accepted')
            ->exists();

        if ($hasRequest) return true;

        // Old system fallback: direct event_id on user
        if ($this->event_id && $this->event_id == $eventId) return true;

        return false;
    }

    // ─── Backward-Compatible Accessors (Safety Net) ──────────────────────────
    // These ensure any code referencing old doc_* columns still works via
    // transparent delegation to the user_documents relationship.

    /**
     * Get a specific document by type.
     * Used internally by the backward-compat accessors.
     */
    public function getDocument(string $type): ?UserDocument
    {
        // Use loaded relation if available to avoid N+1
        if ($this->relationLoaded('documents')) {
            return $this->documents->firstWhere('document_type', $type);
        }
        return $this->documents()->where('document_type', $type)->first();
    }

    // ── File path accessors ──
    public function getDocCommercialRegisterAttribute()
    {
        return $this->getDocument('commercial_register')?->file_path;
    }
    public function getDocTaxNumberAttribute()
    {
        return $this->getDocument('tax_number')?->file_path;
    }
    public function getDocArticlesOfAssociationAttribute()
    {
        return $this->getDocument('articles_of_association')?->file_path;
    }
    public function getDocPracticeLicenseAttribute()
    {
        return $this->getDocument('practice_license')?->file_path;
    }

    // ── Status accessors ──
    public function getDocCommercialRegisterStatusAttribute()
    {
        return $this->getDocument('commercial_register')?->status ?? 'pending';
    }
    public function getDocTaxNumberStatusAttribute()
    {
        return $this->getDocument('tax_number')?->status ?? 'pending';
    }
    public function getDocArticlesOfAssociationStatusAttribute()
    {
        return $this->getDocument('articles_of_association')?->status ?? 'pending';
    }
    public function getDocPracticeLicenseStatusAttribute()
    {
        return $this->getDocument('practice_license')?->status ?? 'pending';
    }

    // ── Note accessors ──
    public function getDocCommercialRegisterNoteAttribute()
    {
        return $this->getDocument('commercial_register')?->note;
    }
    public function getDocTaxNumberNoteAttribute()
    {
        return $this->getDocument('tax_number')?->note;
    }
    public function getDocArticlesOfAssociationNoteAttribute()
    {
        return $this->getDocument('articles_of_association')?->note;
    }
    public function getDocPracticeLicenseNoteAttribute()
    {
        return $this->getDocument('practice_license')?->note;
    }
}
