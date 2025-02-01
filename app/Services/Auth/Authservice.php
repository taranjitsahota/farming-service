<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public static function  registerSuperadminAdmin($request)
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            DB::commit();
            
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public static function registerUser($request)
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'pin' => Hash::make($request->pin), // Hash the pin
            ]);

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


            return [
                'user' => $user,
            ];
        }

    } catch (\Exception $e) {
        return false;
    }

    }
}
