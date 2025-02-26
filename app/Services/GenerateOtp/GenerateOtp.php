<?php

namespace App\Services\GenerateOtp;

use App\Services\SendEmail\SendOtpEmail;
use Exception;

class GenerateOtp
{
    public static function GenereateOtp(){
    try{
        $otp = rand(100000, 999999);
        return $otp;
    }catch(Exception $e){
        return false;
    }
    }
}