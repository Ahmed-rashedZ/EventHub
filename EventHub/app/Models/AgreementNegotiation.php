<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementNegotiation extends Model
{
    protected $fillable = [
        'sponsorship_request_id',
        'status',
        'last_submitted_by',
        'final_notes',
    ];

    public function sponsorshipRequest()
    {
        return $this->belongsTo(SponsorshipRequest::class);
    }

    public function versions()
    {
        return $this->hasMany(AgreementVersion::class, 'negotiation_id')->orderBy('version_number');
    }

    public function latestVersion()
    {
        return $this->hasOne(AgreementVersion::class, 'negotiation_id')->latestOfMany('version_number');
    }

    public function lastSubmitter()
    {
        return $this->belongsTo(User::class, 'last_submitted_by');
    }
}
