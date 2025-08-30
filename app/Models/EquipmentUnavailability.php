<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentUnavailability extends Model
{
    use HasFactory;
    protected $table = 'equipment_unavailability';
    protected $fillable = ['unit_id', 'start_at', 'end_at', 'reason'];
    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime'];

    public function unit()
    {
        return $this->belongsTo(EquipmentUnit::class, 'unit_id');
    }

    public function scopeOverlapping($q, $start, $end)
    {
        return $q->where('start_at', '<', $end)->where('end_at', '>', $start);
    }
}
