<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiTrainingLog extends Model
{
    protected $fillable = ['event_id', 'actual_attendance', 'ai_response', 'sent_at'];

    protected $casts = [
        'ai_response' => 'array',
        'sent_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
