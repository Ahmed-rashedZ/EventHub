<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorshipRequest extends Model
{
    protected $fillable = ['event_id', 'sponsor_id', 'event_manager_id', 'initiator', 'message', 'status'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'event_manager_id');
    }
}
