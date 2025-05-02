<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'user_id', 'service_id', 'slot_date', 'start_time',"crop_id","area_id", 'end_time','created_at', 'updated_at'];

}
