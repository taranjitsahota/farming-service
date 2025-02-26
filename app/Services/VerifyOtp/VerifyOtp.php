<?php

namespace App\Services\VerifyOtp;

use App\Models\Otpverification;

class VerifyOtp
{
    public static function verifyOtp($userId, $otp)
    {
        $userOtp = Otpverification::where('user_id',$userId)->first();

        if (!$userOtp || $userOtp->otp !== $otp || $userOtp->expires_at < now()) {
            return false;
        }

        $userOtp->update([
            'otp' => null,
            'expires_at' => null,
        ]);
        return $userOtp;
    }
}