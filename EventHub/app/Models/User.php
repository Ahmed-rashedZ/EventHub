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
        'password',
        'role',
        'event_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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
}
