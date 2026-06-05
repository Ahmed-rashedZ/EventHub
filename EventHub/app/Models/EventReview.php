<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'rejection_reason',
        'review_message',
        'review_fields',
        'review_status',
        'cancellation_reason',
        'cancellation_rejection_reason',
    ];

    protected $casts = [
        'review_fields' => 'array',
    ];

    /**
     * The event this review belongs to.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
