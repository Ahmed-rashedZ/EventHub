<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['event_id', 'user_id', 'qr_code', 'status', 'ticket_number'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceLog()
    {
        return $this->hasOne(AttendanceLog::class);
    }

    /**
     * All attendance logs (for multi-day events).
     * Each day the ticket is scanned creates a new log entry.
     */
    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
