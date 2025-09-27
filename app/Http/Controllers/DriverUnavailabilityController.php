<?php

namespace App\Http\Controllers;

use App\Models\DriverUnavailability;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
                    'partner_id' => $item->partner_id,
                    'partner_name' => $item->partner->user->name,
                    'driver_id' => $item->driver_id,
                    'driver_name' => $item->driver->user->name,
                    'shift' => $item->shift,
                    'leave_type' => $item->leave_type,
                    'start_at' => $item->start_at ? $item->start_at->format('Y-m-d') : null,
                    'end_at' => $item->end_at ? $item->end_at->format('Y-m-d') : null,
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
            'partner_id' => 'required|exists:partners,id',
            'driver_id' => 'required|exists:drivers,id',
            'shift' => 'required_if:leave_type,shift|nullable|in:first,second',
            'leave_type' => 'required|in:single_day,shift,long_leave',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'reason' => 'required|string',
        ]);
        try {

            $exists = DriverUnavailability::where('driver_id', $request->driver_id)
                ->where('start_at', '<=', $request->start_at)
                ->where('end_at', '>=', $request->end_at)
                ->exists();

            if ($exists) {
                return $this->responseWithError('Driver Unavailability already exists', 422);
            }

            $data = DriverUnavailability::create($request->all());
            return $this->responseWithSuccess($data, 'Driver Unavailability created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 'Driver Unavailability create failed', 422);
        } catch (Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Driver Unavailability create failed');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $driverUnavailabilities = DriverUnavailability::find($id);
            return $this->responseWithSuccess($driverUnavailabilities, 'Driver Unavailability fetched successfully', 200);
        } catch (Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Driver Unavailability fetch failed');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'partner_id' => 'required|exists:partners,id',
            'leave_type' => 'required|in:single_day,shift,long_leave',
            'shift' => 'required_if:leave_type,shift|nullable|in:first,second',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'reason' => 'required|string',
        ]);
        try {

            $startDate = Carbon::parse($request->start_at)->startOfDay();
            $endDate   = Carbon::parse($request->end_at)->endOfDay();
            $driverUnavailabilities = DriverUnavailability::find($id);

            $exists = DriverUnavailability::where('driver_id', $request->driver_id)
                ->where('id', '!=', $id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_at', '<=', $endDate)
                        ->where('end_at', '>=', $startDate);
                })
                ->exists();

            if ($exists) {
                return $this->responseWithError('Driver Unavailability already exists', 422);
            }
            $driverUnavailabilities->update($request->all());
            return $this->responseWithSuccess($driverUnavailabilities, 'Driver Unavailability updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 'Driver Unavailability update failed', 422);
        } catch (Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Driver Unavailability update failed');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $driverUnavailabilities = DriverUnavailability::find($id);
            $driverUnavailabilities->delete();
            return $this->responseWithSuccess($driverUnavailabilities, 'Driver Unavailability deleted successfully', 200);
        } catch (Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Driver Unavailability delete failed');
        }
    }
}
