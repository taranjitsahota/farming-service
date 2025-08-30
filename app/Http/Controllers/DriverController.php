<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = User::role('driver')->with('partner', 'driver')->get();

            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->driver->id,
                    'name' => $item->name,
                    'email' => $item->email,
                    'phone' => $item->phone,
                    'partner_id' => $item->driver->partner_id,
                    'partner_name' => $item->driver->partner->user->name,
                    'license_number' => $item->driver->license_number,
                    'experience_years' => $item->driver->experience_years,
                    'status' => $item->driver->status
                ];
            });

            return $this->responseWithSuccess($formattedData, 'Driver fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Driver not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'phone' => 'required|unique:users,phone|regex:/^\+?[1-9]\d{1,14}$/',
            'partner_id' => 'required|exists:partners,id',
            'license_number' => 'required|unique:drivers',
            'experience_years' => 'nullable|numeric',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
            ]);

            $user->assignRole('driver');

            $driver = Driver::create([
                'user_id' => $user->id,
                'partner_id' => $request->partner_id,
                'license_number' => $request->license_number,
                'experience_years' => $request->experience_years ?? 0,
                'status' => 'active',
            ]);

            return $this->responseWithSuccess($user, 'Driver created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = Driver::find($id);
            return $this->responseWithSuccess($data, 'Driver fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Driver not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $driver = Driver::find($id);

        $userId = $driver->user_id;

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $userId,
            'phone' => 'required|unique:users,phone,' . $userId,
            'partner_id' => 'required|exists:partners,id',
            'experience_years' => 'nullable|numeric',
            'license_number' => 'required|unique:drivers,license_number,' . $id,
            'status' => 'required|in:active,inactive,suspended'
        ]);
        try {

            DB::beginTransaction();

            $user = $driver->user;

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            $driver->update([
                'partner_id' => $request->partner_id,
                'experience_years' => $request->experience_years,
                'license_number' => $request->license_number,
                'status' => $request->status,
            ]);

            return $this->responseWithSuccess($driver, 'Driver updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $driver = Driver::findOrFail($id);

            // Remove driver role from the user
            $driver->user->removeRole('driver');

            $partner = $driver->partner;

            // Save user_id before deleting
            $driverUserId = $driver->user_id;

            // Delete the driver record
            $driver->delete();

            if ($partner) {
                // CASE 1: If the deleted driver is the partner himself
                if ($partner->user_id == $driverUserId) {
                    $partner->update(['is_driver' => false]);
                }

                // CASE 2: If partner had no drivers left at all (edge case safety)
                if ($partner->drivers()->count() === 0) {
                    $partner->update(['is_driver' => false]);
                }
            }

            return $this->responseWithSuccess(null, 'Driver deleted successfully and partner updated if needed', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500);
        }
    }
}
