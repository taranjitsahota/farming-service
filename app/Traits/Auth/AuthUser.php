<?php

namespace App\Traits\Auth;

use App\Services\Auth\AuthService;

trait AuthUser
{
    public function processRegistration($request)
    {
        try{
            AuthService::registerUser($request);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

    public function processLogin($request)
    {
        try {
            $result = AuthService::loginUser($request);

            if ($result) {
                return $result;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}