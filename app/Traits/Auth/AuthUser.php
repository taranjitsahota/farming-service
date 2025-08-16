<?php

namespace App\Traits\Auth;

use App\Models\OtpVerification;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\CompleteProfile\Completeprofile;
use Illuminate\Support\Facades\Hash;

trait AuthUser
{
    
    public function register($request,$role)
    {
        try{

            return AuthService::register($request, $role);


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

            $user = User::where('phone', $request->phone)->where('is_verified', true)->first();

            if (!$user) {
                return false;
            }

            if (!Hash::check($request->password, $user->password)) {
                return false;
            }

            $token = $user->createToken('YourAppName')->plainTextToken;
            $trimmedToken = explode('|', $token)[1];

            return [$user, $trimmedToken];

        } catch (\Exception $e) {
            return false;
        }
    }
        
    public function isOtpRequired($user, $browserHash)
    {
        $lastVerification = OtpVerification::where('user_id', $user->id)
            ->where('browser_hash', $browserHash)
            ->latest()
            ->first();

        if (!$lastVerification) {
            return true; // No previous verification, OTP required
        }

        $otpExpired = now()->diffInDays($lastVerification->verified_at) > 7;
        $browserChanged = $lastVerification->browser_hash !== $browserHash;

        return $otpExpired || $browserChanged;
    }
        
    public function storeOtpVerification($user, $otp)
    {
        OtpVerification::updateOrCreate(
            ['user_id' => $user],
            [
                'otp' => $otp,
                'expires_at' => now()->addMinutes(5),
            ]
        );
    }

}