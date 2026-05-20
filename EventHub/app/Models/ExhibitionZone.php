<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExhibitionZone extends Model
{
    protected $fillable = ['event_id', 'name'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function booths()
    {
        return $this->hasMany(ExhibitionBooth::class, 'exhibition_zone_id');
    }
}
