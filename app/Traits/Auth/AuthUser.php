<?php

namespace App\Traits\Auth;

use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\CompleteProfile\Completeprofile;
use App\Services\GenerateOtp\GenerateOtpMail;
use Illuminate\Support\Facades\Hash;

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
        public function processUser($request)
    {
        try {

            $user = User::where('contact_number', $request->contact_number)->first();

            if (!$user) {
                return false;
            }

            if (!Hash::check($request->pin, $user->pin)) {
                return false;
            }

            $token = $user->createToken('YourAppName')->plainTextToken;
            $trimmedToken = explode('|', $token)[1];

            return [$user, $trimmedToken];

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