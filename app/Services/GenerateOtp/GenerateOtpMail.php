<?php

namespace App\Services\GenerateOtp;

use App\Services\SendEmail\SendOtpEmail;

class GenerateOtpMail
{
    public static function GenereateOtp($user,$otp){
        
    
        // Store OTP and its expiry time (5 minutes from now)
        $user->otp = $otp;
        $user->otp_expiry = now()->addMinutes(5);  // Set OTP expiry time (1 minutes)
        $user->save();

        // Send OTP email
        SendOtpEmail::SendOtpMail($user, $otp);

        return true;
    }
}