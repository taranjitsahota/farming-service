<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price_per_kanal',
        'min_kanals',
        'upfront_percentage',
        'emi_months',
        'razorpay_plan_id',
        'services',
        'benefits',
        'status',
    ];

    protected $casts = [
        'services' => 'array',
        'benefits' => 'array',
        'status' => 'boolean',
    ];
}
