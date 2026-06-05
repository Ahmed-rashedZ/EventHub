<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'ministry_document_path',
        'external_schedule',
        'internal_schedule',
        'agenda',
        'published_schedule',
    ];

    protected $casts = [
        'external_schedule' => 'array',
        'internal_schedule' => 'array',
        'agenda' => 'array',
        'published_schedule' => 'array',
    ];

    /**
     * The event this schedule belongs to.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
