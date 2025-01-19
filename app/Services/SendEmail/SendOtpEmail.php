<?php

namespace App\Services\SendEmail;

use Illuminate\Support\Facades\Mail;

class SendOtpEmail
{
    public static function  SendOtpMail($user, $otp)
    {
        try {
            Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Email Verification OTP');
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}