<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachment_name',
        'attachment_type',
        'vehicle_id',
        'is_enabled',
    ];

    // Define the relationship with Vehicle
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    
}
