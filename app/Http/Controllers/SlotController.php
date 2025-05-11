<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class SlotController extends Controller
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
        //
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
        $validated = $request->validate([
            'slot_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        if(!$id){
            return $this->responseWithError('Slot id is required', 422);
        }

        $booking = Booking::findOrFail($id);
        $booking->update($validated);

        return $this->responseWithSuccess($booking, 'Slot updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
