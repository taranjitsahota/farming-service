<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

            $user = User::create($userData);
    
            if ($role) {
                $user->assignRole($role);
            }
    
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
