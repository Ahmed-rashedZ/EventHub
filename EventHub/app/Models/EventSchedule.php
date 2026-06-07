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

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($schedule) {
            $schedule->syncParentEventTimes();
        });
    }

    public function syncParentEventTimes()
    {
        $event = $this->event;
        if (!$event) {
            return;
        }

        $publishedSchedule = $this->published_schedule;
        if ($publishedSchedule && is_array($publishedSchedule) && count($publishedSchedule) > 0) {
            $dates = collect($publishedSchedule)->pluck('date')->sort()->values();
            if ($dates->count() > 0) {
                $firstDate = $dates->first();
                $lastDate = $dates->last();

                $firstSlot = collect($publishedSchedule)->where('date', $firstDate)->first();
                $lastSlot = collect($publishedSchedule)->where('date', $lastDate)->first();

                $startTimeStr = isset($firstSlot['start_time']) ? $firstSlot['start_time'] : \Carbon\Carbon::parse($event->start_time)->format('H:i:s');
                $endTimeStr = isset($lastSlot['end_time']) ? $lastSlot['end_time'] : \Carbon\Carbon::parse($event->end_time)->format('H:i:s');

                $newStartTime = \Carbon\Carbon::parse($firstDate . ' ' . $startTimeStr);
                $newEndTime = \Carbon\Carbon::parse($lastDate . ' ' . $endTimeStr);

                // Update event record
                $event->update([
                    'start_time' => $newStartTime,
                    'end_time' => $newEndTime,
                ]);
            }
        }
    }

    /**
     * The event this schedule belongs to.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
