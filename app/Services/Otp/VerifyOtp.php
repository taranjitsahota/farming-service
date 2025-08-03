<?php

namespace App\Services\Otp;

use App\Models\OtpVerification;

class VerifyOtp
{
    public static function verifyOtp($userId, $otp)
    {
        $userOtp = OtpVerification::where('user_id',$userId)->first();

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