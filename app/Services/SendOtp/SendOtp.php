<?php

namespace App\Services\SendOtp;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;

class SendOtp
{
    public static function  SendOtpMail($email, $otp)
    {
        try {
            Mail::raw("Your OTP is: $otp", function ($message) use ($email) {
                $message->to($email)
                        ->subject('Email Verification OTP');
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public static function sendOtpPhone($number, $otp)
    {
        Log::info('sms send' . $number);
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $twilio = new Client($sid, $token);

        // Send the OTP message
        $message = $twilio->messages->create($number, [
            'from' => env('TWILIO_FROM'),
            'body' => "Your OTP is: $otp"
        ]);
        Log::info('sms send' . $message);
        // Return the message SID for tracking purposes
        return $message->sid;
    }
}