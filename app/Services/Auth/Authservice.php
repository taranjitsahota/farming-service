<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserInfo;
use App\Services\GenerateOtp\GenerateOtp;
use App\Services\GenerateOtp\GenerateOtpMail;
use App\Services\SendEmail\SendOtpEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class AuthService
{
    public static function register($request,$role = null)
    {   
        try {
            DB::beginTransaction();

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'substation_id' => $request->substation_id, 
                'phone' => $request->phone,
            ];
    
            if ($role) {
                $userData['role'] = $role;
            }
    
            $user = User::create($userData);
    
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }


    public static function loginAdminSuperadmin($request)
    {
        try {

            $credentials = $request->only(['email', 'password']);
            
            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                return $user;
            }

        } catch (\Exception $e) {
            return false;
        }

    }
}
