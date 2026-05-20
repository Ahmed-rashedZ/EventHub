<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExhibitionApplication extends Model
{
    protected $fillable = [
        'event_id',
        'company_id',
        'event_manager_id',
        'initiator',
        'message',
        'status',
        'booth_preference',
        'product_category',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'event_manager_id');
    }

    public function booth()
    {
        return $this->hasOne(ExhibitionBooth::class, 'application_id');
    }

    public function negotiation()
    {
        return $this->hasOne(AgreementNegotiation::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }
}
