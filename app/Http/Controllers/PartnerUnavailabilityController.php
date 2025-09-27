<?php

namespace App\Http\Controllers;

use App\Models\PartnerUnavailability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class PartnerUnavailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = PartnerUnavailability::with('partner')->get();
            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'partner_name' => $item->partner->user->name,
                    'partner_id' => $item->partner_id,
                    'leave_type' => $item->leave_type,
                    'shift' => $item->shift,
                    'start_at' => $item->start_at ? $item->start_at->format('Y-m-d') : null,
                    'end_at' => $item->end_at ? $item->end_at->format('Y-m-d') : null,
                    'reason' => $item->reason,
                ];
            });
            return $this->responseWithSuccess($formattedData, 'Partner Unavailability fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Unavailability not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'leave_type' => 'required|in:single_day,shift,long_leave',
            'shift' => 'required_if:leave_type,shift|nullable|in:first,second',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'reason' => 'required|string',
        ]);
        try {
            $exists = PartnerUnavailability::where('partner_id', $request->partner_id)
                ->where('start_at', '<=', $request->start_at)
                ->where('end_at', '>=', $request->end_at)
                ->exists();

            if ($exists) {
                return $this->responseWithError('Partner Unavailability already exists', 422);
            }

            $data = PartnerUnavailability::create($request->all());
            return $this->responseWithSuccess($data, 'Partner Unavailability created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Unavailability not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = PartnerUnavailability::find($id);
            return $this->responseWithSuccess($data, 'Partner Unavailability fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Unavailability not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
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
            $data = PartnerUnavailability::findOrFail($id);

            $exists = PartnerUnavailability::where('partner_id', $request->partner_id)
                ->where('id', '!=', $id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_at', '<=', $endDate)
                        ->where('end_at', '>=', $startDate);
                })
                ->exists();

            if ($exists) {
                return $this->responseWithError('Partner Unavailability already exists', 422);
            }

            $data->update($request->all());
            return $this->responseWithSuccess($data, 'Partner Unavailability updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Unavailability not found');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = PartnerUnavailability::find($id);
            $data->delete();
            return $this->responseWithSuccess($data, 'Partner Unavailability deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Unavailability not found');
        }
    }
}
