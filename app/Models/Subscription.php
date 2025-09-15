<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'subscription_plan_id',
        'user_id',
        'razorpay_subscription_id',
        'plan_type',
        'location',
        'land_area',
        'kanals',
        'price_per_kanal',
        'total_price',
        'start_date',
        'end_date',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
