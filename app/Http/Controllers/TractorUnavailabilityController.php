<?php

namespace App\Http\Controllers;

use App\Models\Tractor;
use App\Models\TractorUnavailability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TractorUnavailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = TractorUnavailability::with('tractor.partner')->get();
            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tractor_id' => $item->tractor_id,
                    'user_name' => $item->tractor->partner->user->name,
                    'partner_id' => $item->tractor->partner_id,
                    'tractor_name' => $item->tractor->name,
                    'start_at' => $item->start_at ? $item->start_at->format('Y-m-d') : null,
                    'end_at' => $item->end_at ? $item->end_at->format('Y-m-d') : null,
                    'leave_type' => $item->leave_type,
                    'shift' => $item->shift,
                    'reason' => $item->reason,
                ];
            });
            return $this->responseWithSuccess($formattedData, 'Tractor Unavailability fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor Unavailability not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'tractor_id' => 'required|exists:tractors,id',
            'shift' => 'required_if:leave_type,shift|nullable|in:first,second',
            'leave_type' => 'required|in:single_day,shift,long_leave',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'reason' => 'required|string',
        ]);
        try {

            $exists = TractorUnavailability::where('tractor_id', $request->tractor_id)
                ->where('start_at', '<=', $request->start_at)
                ->where('end_at', '>=', $request->end_at)
                ->exists();

            if ($exists) {
                return $this->responseWithError('Tractor Unavailability already exists', 422);
            }

            $data = TractorUnavailability::create($request->all());
            return $this->responseWithSuccess($data, 'Tractor Unavailability created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor Unavailability not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = TractorUnavailability::find($id);
            return $this->responseWithSuccess($data, 'Tractor Unavailability fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor Unavailability not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'tractor_id' => 'required|exists:tractors,id',
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

            $exists = TractorUnavailability::where('tractor_id', $request->tractor_id)
                ->where('id', '!=', $id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_at', '<=', $endDate)
                        ->where('end_at', '>=', $startDate);
                })
                ->exists();

            if ($exists) {
                return $this->responseWithError('Tractor Unavailability already exists', 422);
            }

            $data = TractorUnavailability::find($id);
            $data->update($request->all());
            return $this->responseWithSuccess($data, 'Tractor Unavailability updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor Unavailability not found');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = TractorUnavailability::find($id);
            $data->delete();
            return $this->responseWithSuccess($data, 'Tractor Unavailability deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor Unavailability not found');
        }
    }
}
