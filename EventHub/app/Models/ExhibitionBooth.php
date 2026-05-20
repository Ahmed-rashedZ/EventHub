<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExhibitionBooth extends Model
{
    protected $fillable = [
        'application_id',
        'event_id',
        'company_id',
        'booth_number',
        'booth_size',
        'booth_fee',
        'rank',
        'rank_order',
        'notes',
    ];

    protected $casts = [
        'booth_fee' => 'decimal:2',
        'rank_order' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(ExhibitionApplication::class, 'application_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }
}
