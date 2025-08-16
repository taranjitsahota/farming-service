<?php

namespace App\Services\InterestedUsers;

use App\Models\InterestedUser;

class InterestedUsers
{
    public function createInterestedUser($user, $data)
    {
        try {
            $exists = InterestedUser::where('user_id', $user->id)
                ->where('village_id', $data['village_id'])
                ->exists();

            if ($exists) {
                return false;
            }

            InterestedUser::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email ?? null,
                'state_id' => $data['state_id'],
                'district_id' => $data['district_id'],
                'tehsil_id' => $data['tehsil_id'],
                'village_id' => $data['village_id'],
                'type' => 'app'
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
