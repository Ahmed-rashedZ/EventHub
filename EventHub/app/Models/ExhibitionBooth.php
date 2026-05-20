<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExhibitionBooth extends Model
{
    protected $fillable = ['exhibition_zone_id', 'booth_number', 'size', 'exhibition_application_id'];

    public function zone()
    {
        return $this->belongsTo(ExhibitionZone::class, 'exhibition_zone_id');
    }

    public function application()
    {
        return $this->belongsTo(ExhibitionApplication::class, 'exhibition_application_id');
    }
}
