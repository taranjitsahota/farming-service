<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'user_id','price','status','user_note','payment_method', 'payment_id','address','longitude', 'latitude', 'reserved_until', 'paid_at', 'service_id', 'slot_date', 'start_time',"crop_id","service_area_id",'area', 'end_time','duration','created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Relationship with Crop
    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    // Relationship with Service Area
    public function servicearea()
    {
        return $this->belongsTo(ServiceArea::class, 'service_area_id');
    }

}
