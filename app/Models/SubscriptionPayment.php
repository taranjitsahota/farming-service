<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'subscription_id',
        'razorpay_payment_id',
        'amount',
        'currency',
        'status',
        'paid_at',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];

    // relation: each payment belongs to one subscription
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
