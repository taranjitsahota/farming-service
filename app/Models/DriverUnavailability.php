<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverUnavailability extends Model
{
    use HasFactory;

    protected $table = 'driver_unavailability';
    protected $fillable = ['partner_id', 'driver_id', 'start_at', 'end_at', 'leave_type', 'shift', 'reason'];
    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];


    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeOverlapping($q, $start, $end)
    {
        return $q->where('start_at', '<', $end)->where('end_at', '>', $start);
    }
}
