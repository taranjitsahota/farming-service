<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Riverline\MultiPartParser\Part;

class PartnerUnavailability extends Model
{
    use HasFactory;
    protected $table = 'partner_unavailability';
    protected $fillable = ['partner_id', 'start_at', 'end_at', 'reason'];
    protected $casts = ['start_at' => 'date:Y-m-d', 'end_at' => 'date:Y-m-d'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function scopeOverlapping($q, $start, $end)
    {
        return $q->where('start_at', '<', $end)->where('end_at', '>', $start);
    }
}
