<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'status',
        'morning_start',
        'morning_end',
        'evening_start',
        'evening_end'
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function maintenancePeriods()
    {
        return $this->hasMany(VenueMaintenancePeriod::class);
    }

    /**
     * Check if venue is under maintenance on a given date.
     */
    public function isUnderMaintenance($date): bool
    {
        if ($this->status === 'maintenance') {
            return true;
        }

        $date = Carbon::parse($date)->format('Y-m-d');
        return $this->maintenancePeriods()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }

    /**
     * Get all maintenance dates as a flat array of Y-m-d strings.
     */
    public function getMaintenanceDates(): array
    {
        $dates = [];
        foreach ($this->maintenancePeriods as $period) {
            $start = Carbon::parse($period->start_date);
            $end   = Carbon::parse($period->end_date);
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $dates[] = $d->format('Y-m-d');
            }
        }
        return array_unique($dates);
    }
}
