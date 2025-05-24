<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DriverController extends Controller
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function AssignDriver(Request$request)
    {
        try {
        $request->validate([
            'driver_id' => 'required',
            'booking_id' => 'required'
        ]);

        $booking = Booking::find($request->booking_id);
        $booking->driver_id = $request->driver_id;
        $booking->admin_note = $request->admin_note;
        $booking->save();

        return $this->responseWithSuccess($booking, 'Driver assigned successfully', 200);
        }catch (ValidationException $e) {
            return $this->responseWithError($e->errors(), 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500);
        }
    }
}
