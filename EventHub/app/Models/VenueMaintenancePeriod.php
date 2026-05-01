<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VenueMaintenancePeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'start_date',
        'end_date',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
