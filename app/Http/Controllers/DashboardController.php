<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Subscriptions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\CodeCoverage\Driver\Driver;

class DashboardController extends Controller
{
   public function getDashboardMetrics()
{
    try {
        $bookings = Booking::where('booking_status', 'completed')->count();
        // $subscriptions = Subscriptions::where('status', 'active')->count();
        $revenue = Booking::where('booking_status', 'completed')->sum('price');
        $drivers = User::where('role', 'driver')->count();
        $farmers = User::where('role', 'user')->count();
        
        // Example top revenue locations
        // $locations = Booking::select('location', DB::raw('SUM(price) as total'))
        //     ->groupBy('location')
        //     ->orderByDesc('total')
        //     ->limit(10)
        //     ->get()
        //     ->map(function ($loc) {
        //         return [
        //             'name' => $loc->location,
        //             // 'imageUrl' => 'https://placehold.co/100x60/EEE/31343C', // update with real image
        //         ];
        //     });

        $data = compact('bookings', 'revenue', 'drivers', 'farmers');

        return $this->responseWithSuccess($data, 'Dashboard metrics fetched successfully');
    } catch (\Exception $e) {
        return $this->responseWithError('Failed to fetch dashboard metrics', 500, $e->getMessage());
    }
}

}
