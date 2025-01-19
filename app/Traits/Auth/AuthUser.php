<?php

namespace App\Traits\Auth;

use App\Services\Auth\AuthService;
use App\Services\GenerateOtp\GenerateOtpMail;

trait AuthUser
{
    public function processRegistration($request)
    {
        try{

            $user = AuthService::registerUser($request);

            return true;

        }catch(\Exception $e){
            return false;
        }
    }

        public function processLogin($request)
    {
        try {
            $user = AuthService::loginUser($request);

            return $user;
        } catch (\Exception $e) {
            return false;
        }
    }
}