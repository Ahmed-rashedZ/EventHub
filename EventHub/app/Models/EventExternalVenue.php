<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventExternalVenue extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'venue_name',
        'venue_location',
        'booking_proof_path',
        'booking_date',
        'period',
    ];

    protected $casts = [
        'booking_date' => 'date',
    ];

    /**
     * The event this external venue belongs to.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
