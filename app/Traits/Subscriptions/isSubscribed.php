<?php

namespace App\Traits\Subscriptions;

use App\Models\User;
use App\Models\Subscriptions;

trait isSubscribed
{
    /**
     * Check if a user is subscribed to a specific service.
     * 
     * @param User $user
     * @param int $requiredServiceId
     * @param float $requiredArea
     * @return bool
     */
    public function isSubscribed(User $user, $requiredServiceId, $requiredArea): bool
    {
        $subscription = Subscriptions::where('user_id', $user->id)
            ->where('start_date', '<=', today())
            ->where('end_date', '>=', today())
            ->first();

        if (!$subscription) {
            return false;
        }

        // Optional: validate service vs plan type (e.g., sugarcane vs general)
        return $requiredArea <= $subscription->land_area;
    }
}
