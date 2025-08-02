<?php

namespace App\Services\SendOtp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $url = env('MSG91_FLOW_URL');
        $authKey = env('MSG91_AUTHKEY');
        $templateId = env('MSG91_TEMPLATE_ID_OTP');

        $payload = [
            'template_id' => $templateId,
            'recipients' => [
                [
                    'mobiles' => $number,
                    'var1' => $otp,
                ],
            ],
        ];

        $response = Http::withHeaders([
            'accept' => 'application/json',
            'authkey' => $authKey,
            'content-type' => 'application/json'
        ])->post($url, $payload);

        return $response->successful();
    }
}