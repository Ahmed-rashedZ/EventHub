<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementVersion extends Model
{
    protected $fillable = [
        'negotiation_id',
        'version_number',
        'file_path',
        'uploaded_by',
        'action',
        'message',
    ];

    public function negotiation()
    {
        return $this->belongsTo(AgreementNegotiation::class, 'negotiation_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
