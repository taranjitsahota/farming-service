<?php

namespace App\Traits\Auth;

use App\Services\Auth\AuthService;
use App\Services\CompleteProfile\Completeprofile;
use App\Services\GenerateOtp\GenerateOtpMail;

trait AuthUser
{
    public function RegistrationSuperadminAdmin($request)
    {
        try{

            $user = AuthService::registerSuperadminAdmin($request);

            return true;

        }catch(\Exception $e){
            return false;
        }
    }

    public function processRegistrationUser($request)
    {
        try{

            $user = AuthService::registerUser($request);

            return $user;

        }catch(\Exception $e){
            return false;
        }
    }

        public function processLoginAdminSuperadmin($request)
    {
        try {
            $user = AuthService::loginAdminSuperadmin($request);

            return $user;
        } catch (\Exception $e) {
            return false;
        }
    }
        public function processcompleteUserProfile($request)
    {
        try {
            $user = Completeprofile::completeUserProfile($request);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}