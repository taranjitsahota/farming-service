<?php

namespace App\Http\Controllers;

use App\Models\DriverUnavailability;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DriverUnavailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $driverUnavailabilities = DriverUnavailability::all();

            $formattedData = $driverUnavailabilities->map(function ($item) {
                return [
                    'id' => $item->id,
                    'driver_id' => $item->driver_id,
                    'driver_name' => $item->driver->user->name,
                    'start_at' => $item->start_at ? $item->start_at->format('Y-m-d') : null,
                    'end_at' => $item->end_at ? $item->start_at->format('Y-m-d') : null,
                    'reason' => $item->reason,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });
            return $this->responseWithSuccess($formattedData, 'Driver Unavailability fetched successfully', 200);
        } catch (Exception $e) {
            return $this->responseWithError($e->getMessage(), 500,  'Driver Unavailability fetch failed');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'driver_id' => 'required',
            'start_at' => 'required',
            'end_at' => 'required',
            'reason' => 'nullable',
        ]);
        try{
            $data = DriverUnavailability::create($request->all());
            return $this->responseWithSuccess($data, 'Driver Unavailability created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 'Driver Unavailability create failed', 422);
        } catch (Exception $e) {
            return $this->responseWithError($e->getMessage(),500, 'Driver Unavailability create failed');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
        $driverUnavailabilities = DriverUnavailability::find($id);
        return $this->responseWithSuccess($driverUnavailabilities, 'Driver Unavailability fetched successfully', 200);
        } catch (Exception $e) {
            return $this->responseWithError($e->getMessage(), 500 , 'Driver Unavailability fetch failed');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'driver_id' => 'required',
            'start_at' => 'required',
            'end_at' => 'required',
            'reason' => 'nullable',
        ]);
        try{
            $driverUnavailabilities = DriverUnavailability::find($id);
            $driverUnavailabilities->update($request->all());
            return $this->responseWithSuccess($driverUnavailabilities, 'Driver Unavailability updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 'Driver Unavailability update failed', 422);
        } catch (Exception $e) {
            return $this->responseWithError($e->getMessage(), 500 , 'Driver Unavailability update failed');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $driverUnavailabilities = DriverUnavailability::find($id);
            $driverUnavailabilities->delete();
            return $this->responseWithSuccess($driverUnavailabilities, 'Driver Unavailability deleted successfully', 200);
        } catch (Exception $e) {
            return $this->responseWithError($e->getMessage(), 500 , 'Driver Unavailability delete failed');
        }
    }
}
