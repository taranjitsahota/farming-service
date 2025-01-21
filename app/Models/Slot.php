<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'crop_id', 'attachment_id', 'vehicle_id',
        'service_id', 'area_id', 'slot_date', 'start_time',
        'end_time', 'status', 'user_note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    
}
