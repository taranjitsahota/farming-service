<?php

namespace App\Http\Controllers;

use App\Models\SuperadminLogs;
use Illuminate\Http\Request;

class SuperadminLogsController extends Controller
{
    public function createSuperAdminlogs($vehicle){
        SuperadminLogs::create([
            'user_id' => auth()->id(),
            'action' => 'Enable Vehicle',
            'target_type' => 'vehicle',
            'target_id' => $vehicle->id,
            'previous_data' => json_encode($vehicle->getOriginal()), // Original data before change
            'new_data' => json_encode($vehicle->toArray()), // Updated data after change
            'ip_address' => request()->ip(),
            'additional_info' => 'Enabled vehicle for pincode 123456',
        ]);
    }

    public function getSuperAdminLogs($vehicle){
        $logs = SuperadminLogs::where('target_type', 'vehicle')
            ->where('target_id', $vehicle->id)
            ->orderBy('created_at', 'desc')
            ->get();

    }
}
