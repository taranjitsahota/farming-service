<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BusinessTiming;
use App\Models\Equipment;
use App\Models\Service;
use Carbon\Carbon;
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

        if (!$id) {
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

    public function getAvailableSlots(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'service_id' => 'required|exists:services,id',
                'equipment_id' => 'required|exists:equipments,id',
                'area' => 'required|numeric|min:1',
                'substation_id' => 'required|exists:substations,id'
            ]);

            $date = Carbon::parse($request->date)->startOfDay();
            $dayOfWeek = $date->format('l');
            $area = $request->area;
            $substationId = $request->substation_id;

            $service = Service::findOrFail($request->service_id);

            $equipment = Equipment::where('id', $request->equipment_id)
                ->where('service_id', $service->id)
                ->where('substation_id', $substationId)
                ->first();

            if (!$equipment) {
                return $this->responseWithError('No equipment linked to this service in the given substation', 422);
            }

            if ($equipment->min_kanal && $area < $equipment->min_kanal) {
                return $this->responseWithError(
                    'Minimum area in kanals is ' . $equipment->min_kanal . ' kanals',
                    422
                );
            }

            // Get business hours
            $businessTiming = BusinessTiming::where('day', $dayOfWeek)->first();
            if (!$businessTiming) {
                return $this->responseWithError('No business hours set for ' . $dayOfWeek, 422);
            }

            $currentDateTime = now();
            // $currentDateTime = Carbon::createFromFormat('Y-m-d H:i', '2025-05-01 12:00'); // Change to now() in production
            $requestedDate = $date->toDateString();
            $currentPlus24 = $currentDateTime->copy()->addDay();

            // Case 1: Booking for the day exactly 24 hours later (same hour)
            if ($requestedDate === $currentPlus24->toDateString()) {
                $businessEndDateTime = Carbon::parse($requestedDate . ' ' . $businessTiming->end_time);

                // If current time + 24hr is already past the business end time, return error
                if ($currentDateTime->copy()->addDay()->gt($businessEndDateTime)) {
                    return $this->responseWithError('Business hours have ended for the selected date.', 422);
                }

                $startReference = $currentDateTime->copy()->addDay();
                $minute = $startReference->minute;

                if ($minute < 30) {
                    $startReference->minute(0);
                } else {
                    $startReference->addHour()->minute(0);
                }

                $dayStartTime = max(
                    Carbon::parse($requestedDate . ' ' . $businessTiming->start_time),
                    $startReference
                );
            }
            // Case 2: Booking for a future day beyond 24 hours
            elseif ($requestedDate > $currentPlus24->toDateString()) {
                $dayStartTime = Carbon::parse($requestedDate . ' ' . $businessTiming->start_time);
            }
            // Invalid Case: Trying to book for today or within 24 hours
            else {
                return $this->responseWithError('Service should be booked at least 24 hours in advance.', 422);
            }

            $dayEndTime = Carbon::parse($requestedDate . ' ' . $businessTiming->end_time);
            $total_minutes = $area * $equipment->minutes_per_kanal;
            $buffer_minutes = 30;

            // Get bookings for the selected day
            $bookings = Booking::where('slot_date', $requestedDate)
                ->where('service_id', $service->id)
                ->where('substation_id', $substationId)
                ->where(function ($q) {
                    $q->where('booking_status', 'confirmed')
                        ->orWhere(function ($q2) {
                            $q2->where('booking_status', 'pending')
                                ->where('reserved_until', '>', now());
                        });
                })
                ->orderBy('start_time')
                ->get()
                ->map(function ($booking) use ($requestedDate, $buffer_minutes) {
                    $booking->start_time = Carbon::parse($requestedDate . ' ' . Carbon::parse($booking->start_time)->format('H:i'))->subMinutes($buffer_minutes);
                    $booking->end_time = Carbon::parse($requestedDate . ' ' . Carbon::parse($booking->end_time)->format('H:i'))->addMinutes($buffer_minutes);
                    return $booking;
                });


            $slots = [];
            $current_start = $dayStartTime->copy();


            while ($current_start->addMinutes(0)->lt($dayEndTime)) {
                $slot_end = $current_start->copy()->addMinutes($total_minutes);

                if ($slot_end->gt($dayEndTime)) {
                    break;
                }

                // $conflict = false;
                // foreach ($bookings as $booking) {
                //     if (
                //         $slot_end->gt($booking->start_time) &&
                //         $current_start->lt($booking->end_time)
                //     ) {
                //         $current_start = $booking->end_time->copy(); // move to next slot after booking
                //         $conflict = true;
                //         break;
                //     }
                // }

                // if (!$conflict) {
                //     $slots[] = [
                //         'start_time' => $current_start->format('H:i'),
                //         'end_time' => $slot_end->format('H:i'),
                //         'duration_required_minutes' => $total_minutes,
                //     ];
                //     $current_start = $slot_end->copy(); // move to next slot
                // }
                $overlappingCount = $bookings->filter(function ($booking) use ($current_start, $slot_end) {
                    return $slot_end->gt($booking->start_time) && $current_start->lt($booking->end_time);
                })->count();

                // If equipment inventory is available, add this slot
                if ($overlappingCount < $equipment->inventory) {
                    $slots[] = [
                        'start_time' => $current_start->format('H:i'),
                        'end_time' => $slot_end->format('H:i'),
                        'duration_required_minutes' => $total_minutes,
                    ];
                }

                // Move to next slot regardless
                $current_start = $slot_end->copy();
            }
            if (!$slots) {
                return $this->responseWithError('No slots available', 422);
            }

            return $this->responseWithSuccess($slots, 'Available slots fetched successfully', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
