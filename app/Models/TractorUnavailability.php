<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TractorUnavailability extends Model
{
    use HasFactory;
    protected $table = 'tractor_unavailability';
    protected $fillable = ['tractor_id','start_at','end_at','reason'];
    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime'];

    public function tractor() { return $this->belongsTo(Tractor::class); }

    public function scopeOverlapping($q, $start, $end) {
        return $q->where('start_at', '<', $end)->where('end_at', '>', $start);
    }
}
