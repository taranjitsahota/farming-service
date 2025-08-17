<?php

namespace App\Models;

use App\Models\Scopes\ServiceRoleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'user_id', 'substation_id' ,'driver_id', 'price','booking_status','payment_status','user_note','admin_note','payment_method', 'payment_id','address','longitude', 'latitude', 'reserved_until', 'paid_at', 'slot_date', 'start_time',"crop_id","service_area_id",'land_area', 'end_time','duration','created_at', 'updated_at'];

     protected static function booted()
    {
        static::addGlobalScope(new ServiceRoleScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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

     public function substation()
    {
        return $this->belongsTo(Substation::class);
    }
}
