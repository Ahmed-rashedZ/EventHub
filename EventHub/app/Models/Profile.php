<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_type',
        // Basic info
        'logo',
        'bio',
        // Company info
        'company_name',
        'company_description',
        // Admin workflow
        'is_approved',
        'is_available',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_available' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * The user this profile belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dynamic contacts (email, phone, social media).
     */
    public function contacts()
    {
        return $this->hasMany(ProfileContact::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Filter to company/sponsor profiles only.
     * Usage: Profile::companies()->get()
     */
    public function scopeCompanies($query)
    {
        return $query->where('profile_type', 'company');
    }

    /**
     * Filter to individual profiles only.
     * Usage: Profile::individuals()->get()
     */
    public function scopeIndividuals($query)
    {
        return $query->where('profile_type', 'individual');
    }

    /**
     * Filter to admin-approved profiles only.
     * Usage: Profile::approved()->get()
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Filter to profiles that are available.
     * Usage: Profile::companies()->available()->get()
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    // ─── Ticket Branding Helper ───────────────────────────────────────────────

    /**
     * Returns the ticket branding display label for a given sponsor tier.
     *
     * Usage: Profile::tierLabel($user->pivot->tier)
     *
     *   diamond → 'logo'           (render logo alongside event logo)
     *   gold    → 'Sponsored by'
     *   silver  → 'Supported by'
     *   bronze  → 'Special thanks to'
     */
    public static function tierLabel(string $tier): string
    {
        return match ($tier) {
            'diamond' => 'logo',
            'gold'    => 'Sponsored by',
            'silver'  => 'Supported by',
            'bronze'  => 'Special thanks to',
            default   => '',
        };
    }

    /**
     * Returns the full display name for this profile.
     * Prefers company_name for company profiles, falls back to the user name.
     */
    public function displayName(): string
    {
        return $this->profile_type === 'company' && $this->company_name
            ? $this->company_name
            : $this->user->name;
    }
}
