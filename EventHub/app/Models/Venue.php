<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
