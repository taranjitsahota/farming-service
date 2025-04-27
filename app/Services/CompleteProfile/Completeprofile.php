<?php

namespace App\Services\CompleteProfile;

use App\Models\UserInfo;
use App\Models\User;

class Completeprofile
{
    public static function completeUserProfile($request)
    {
        try {

            $user = User::find(auth()->id());
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            
            UserInfo::updateOrCreate(
                ['user_id' => $user->id], 
                $request->only([
                     'fathers_name', 'pincode',
                    'village', 'post_office', 'police_station', 'district',
                    'total_servicable_land'
                ])
            );
        
            $user->profile_completed = true;
            $user->save();


            return true;
        }

     catch (\Exception $e) {
        return false;
    }

    }
}