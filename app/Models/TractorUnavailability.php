<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class   TractorUnavailability extends Model
{
    use HasFactory;
    protected $table = 'tractor_unavailability';
    protected $fillable = ['partner_id', 'tractor_id', 'start_at', 'end_at', 'leave_type', 'shift', 'reason'];
    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
    public function tractor()
    {
        return $this->belongsTo(Tractor::class);
    }

    public function scopeOverlapping($q, $start, $end)
    {
        return $q->where('start_at', '<', $end)->where('end_at', '>', $start);
    }
}
