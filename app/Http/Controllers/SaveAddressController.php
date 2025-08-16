<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\SavedFarm;
use Illuminate\Http\Request;

class SaveAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'action' => 'required|in:update_booking,save_farm',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'address' => 'required|string',
            ]);

            if ($request->action === 'update_booking') {
                $request->validate([
                    'booking_id' => 'required|exists:bookings,id',
                ]);

                $booking = Booking::find($request->booking_id);
                $booking->latitude = $request->latitude;
                $booking->longitude = $request->longitude;
                $booking->address = $request->address;
                $booking->save();

                return $this->responseWithSuccess($booking, 'Booking updated successfully', 200);
            }

            if ($request->action === 'save_farm') {
                $request->validate([
                    'user_id' => 'required|exists:users,id',
                    'category' => 'required|string|max:50',
                ]);

                $farm = new SavedFarm();
                $farm->user_id = $request->user_id;
                $farm->latitude = $request->latitude;
                $farm->longitude = $request->longitude;
                $farm->address = $request->address;
                $farm->category = $request->category;
                $farm->save();

                return $this->responseWithSuccess($farm, 'Farm saved successfully', 200);
            }

            return $this->responseWithError('Invalid action');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
