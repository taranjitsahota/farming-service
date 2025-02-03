<?php

namespace App\Traits\SendOtp;

use Twilio\Rest\Client;

trait SendOtp
{
    /**
     * Send OTP via Twilio SMS.
     *
     * @param  string $phone
     * @param  string $otp
     * @return string
     */
    public function sendOtp($user, $otp)
    {

        $user->otp = $otp;
        $user->otp_expiry = now()->addMinutes(5); // Set OTP expiry time (5 minutes)
        $user->save();

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $twilio = new Client($sid, $token);

        // Send the OTP message
        $message = $twilio->messages->create($user->phone, [
            'from' => env('TWILIO_FROM'),
            'body' => "Your OTP is: $otp"
        ]);

        // Return the message SID for tracking purposes
        return $message->sid;
    }
}