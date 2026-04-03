<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'type',
        'value',
    ];

    /**
     * The profile this contact belongs to.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
