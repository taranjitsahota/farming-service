<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getDashboardMetrics()
    {
        try {
            $bookings = Booking::where('booking_status', 'completed')->count();
            $subscriptions = Subscription::where('status', 'active')->count();
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

            $data = compact('bookings', 'revenue', 'subscriptions', 'drivers', 'farmers');

            return $this->responseWithSuccess($data, 'Dashboard metrics fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Failed to fetch dashboard metrics', 500, $e->getMessage());
        }
    }

    public function getBookingsTrend(Request $request)
    {
        $range = $request->input('range', 'monthly'); // 'daily' or 'monthly'

        $query = DB::table('bookings')
            ->selectRaw(
                $range === 'monthly'
                    ? "DATE_FORMAT(created_at, '%Y-%m') as date, COUNT(*) as count"
                    : "DATE(created_at) as date, COUNT(*) as count"
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $data = $query->map(function ($item) {
            return [
                'date' => $item->date,
                'count' => $item->count,
            ];
        });
        return $this->responsewithSuccess($data, 'Bookings trend fetched successfully', 200);
    }
}
