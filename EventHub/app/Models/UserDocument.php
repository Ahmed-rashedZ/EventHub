<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'file_path',
        'status',
        'note',
    ];

    /**
     * The user this document belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
