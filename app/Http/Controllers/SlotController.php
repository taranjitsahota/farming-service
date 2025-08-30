<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BusinessTiming;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\Partner;
use App\Models\Service;
use App\Models\ServiceArea;
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

    // public function getAvailableSlots(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'date' => 'required|date',
    //             'equipment_id' => 'required|exists:equipments,id',
    //             'service_id' => 'required|exists:services,id',
    //             'service_area_id' => 'required|exists:serviceareas,id',
    //             // 'area_id' => 'required|exists:areas,id',
    //             'area' => 'required|numeric|min:1',
    //             'substation_id' => 'required|exists:substations,id'
    //         ]);

    //         $date = Carbon::parse($request->date)->startOfDay();
    //         $dayOfWeek = $date->format('l');
    //         $area = $request->area;
    //         // $substationId = $request->substation_id;

    //         // $serviceArea = ServiceArea::where('service_id', $request->service_id)
    //         //     // ->where('area_id', $request->area_id)
    //         //     ->where('equipment_id', $request->equipment_id)
    //         //     ->where('substation_id', $substationId)
    //         //     ->first();
    //         $serviceAreaId = $request->service_area_id;
    //         // dd($serviceAreaId);
    //         // if (!$serviceAreaId) {
    //         //     return $this->responseWithError('No service area link found for this service, equipment, and substation', 422);
    //         // }

    //         $equipment = Equipment::findOrFail($request->equipment_id);


    //         if (!$equipment) {
    //             return $this->responseWithError('No equipment linked to this service in the given substation', 422);
    //         }

    //         if ($equipment->min_kanal && $area < $equipment->min_kanal) {
    //             return $this->responseWithError(
    //                 'Minimum area in kanals is ' . $equipment->min_kanal . ' kanals',
    //                 422
    //             );
    //         }

    //         // Get business hours
    //         $businessTiming = BusinessTiming::where('day', $dayOfWeek)->first();
    //         if (!$businessTiming) {
    //             return $this->responseWithError('No business hours set for ' . $dayOfWeek, 422);
    //         }

    //         $currentDateTime = now();
    //         // $currentDateTime = Carbon::createFromFormat('Y-m-d H:i', '2025-05-01 12:00'); // Change to now() in production
    //         $requestedDate = $date->toDateString();
    //         $currentPlus24 = $currentDateTime->copy()->addDay();

    //         // Case 1: Booking for the day exactly 24 hours later (same hour)
    //         if ($requestedDate === $currentPlus24->toDateString()) {
    //             $businessEndDateTime = Carbon::parse($requestedDate . ' ' . $businessTiming->end_time);

    //             // If current time + 24hr is already past the business end time, return error
    //             if ($currentDateTime->copy()->addDay()->gt($businessEndDateTime)) {
    //                 return $this->responseWithError('Business hours have ended for the selected date.', 422);
    //             }

    //             $startReference = $currentDateTime->copy()->addDay();
    //             $minute = $startReference->minute;

    //             if ($minute < 30) {
    //                 $startReference->minute(0);
    //             } else {
    //                 $startReference->addHour()->minute(0);
    //             }

    //             $dayStartTime = max(
    //                 Carbon::parse($requestedDate . ' ' . $businessTiming->start_time),
    //                 $startReference
    //             );
    //         }
    //         // Case 2: Booking for a future day beyond 24 hours
    //         elseif ($requestedDate > $currentPlus24->toDateString()) {
    //             $dayStartTime = Carbon::parse($requestedDate . ' ' . $businessTiming->start_time);
    //         }
    //         // Invalid Case: Trying to book for today or within 24 hours
    //         else {
    //             return $this->responseWithError('Service should be booked at least 24 hours in advance.', 422);
    //         }

    //         $dayEndTime = Carbon::parse($requestedDate . ' ' . $businessTiming->end_time);
    //         $total_minutes = $area * $equipment->minutes_per_kanal;
    //         $buffer_minutes = 30;

    //         // Get bookings for the selected day
    //         $bookings = Booking::where('slot_date', $requestedDate)
    //             ->where('service_area_id', $serviceAreaId)
    //             ->where(function ($q) {
    //                 $q->where('payment_status', 'confirmed')
    //                     ->orWhere(function ($q2) {
    //                         $q2->where('payment_status', 'pending')
    //                             ->where('reserved_until', '>', now());
    //                     });
    //             })
    //             ->orderBy('start_time')
    //             ->get()
    //             ->map(function ($booking) use ($requestedDate, $buffer_minutes) {
    //                 $booking->start_time = Carbon::parse($requestedDate . ' ' . Carbon::parse($booking->start_time)->format('H:i'))->subMinutes($buffer_minutes);
    //                 $booking->end_time = Carbon::parse($requestedDate . ' ' . Carbon::parse($booking->end_time)->format('H:i'))->addMinutes($buffer_minutes);
    //                 return $booking;
    //             });


    //         $slots = [];
    //         $current_start = $dayStartTime->copy();


    //         while ($current_start->addMinutes(0)->lt($dayEndTime)) {
    //             $slot_end = $current_start->copy()->addMinutes($total_minutes);

    //             if ($slot_end->gt($dayEndTime)) {
    //                 break;
    //             }

    //             // $conflict = false;
    //             // foreach ($bookings as $booking) {
    //             //     if (
    //             //         $slot_end->gt($booking->start_time) &&
    //             //         $current_start->lt($booking->end_time)
    //             //     ) {
    //             //         $current_start = $booking->end_time->copy(); // move to next slot after booking
    //             //         $conflict = true;
    //             //         break;
    //             //     }
    //             // }

    //             // if (!$conflict) {
    //             //     $slots[] = [
    //             //         'start_time' => $current_start->format('H:i'),
    //             //         'end_time' => $slot_end->format('H:i'),
    //             //         'duration_required_minutes' => $total_minutes,
    //             //     ];
    //             //     $current_start = $slot_end->copy(); // move to next slot
    //             // }
    //             $overlappingCount = $bookings->filter(function ($booking) use ($current_start, $slot_end) {
    //                 return $slot_end->gt($booking->start_time) && $current_start->lt($booking->end_time);
    //             })->count();

    //             // If equipment inventory is available, add this slot
    //             if ($overlappingCount < $equipment->inventory) {
    //                 $slots[] = [
    //                     'start_time' => $current_start->format('H:i'),
    //                     'end_time' => $slot_end->format('H:i'),
    //                     'duration_required_minutes' => $total_minutes,
    //                     'service_area_id' => $serviceAreaId
    //                 ];
    //             }

    //             // Move to next slot regardless
    //             $current_start = $slot_end->copy();
    //         }
    //         if (!$slots) {
    //             return $this->responseWithError('No slots available', 422);
    //         }

    //         return $this->responseWithSuccess($slots, 'Available slots fetched successfully', 200);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return $this->responseWithError('Validation failed', 422, $e->errors());
    //     } catch (\Exception $e) {
    //         return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
    //     }
    // }


    public function getAvailableSlots(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'equipment_type_id' => 'required|exists:equipment_types,id',
                'area_of_land' => 'required|numeric|min:1',
                'area_id' => 'required|exists:areas,id'
            ]);

            $date = Carbon::parse($request->date)->startOfDay();
            $dayOfWeek = $date->format('l');
            $area_of_land = $request->area_of_land;
            $areaId = $request->area_id;
            $equipmentTypeId = $request->equipment_type_id;

            // Get equipment type details
            $equipmentType = EquipmentType::findOrFail($equipmentTypeId);

            // Check minimum area requirement
            if ($equipmentType->min_kanal && $area_of_land < $equipmentType->min_kanal) {
                return $this->responseWithError(
                    'Minimum area required is ' . $equipmentType->min_kanal . ' kanals',
                    422
                );
            }

            // Get business hours for the day
            $businessTiming = BusinessTiming::where('day', $dayOfWeek)->first();
            if (!$businessTiming) {
                return $this->responseWithError('No business hours set for ' . $dayOfWeek, 422);
            }

            // Validate booking time (24 hours advance)
            $currentDateTime = now();
            $requestedDate = $date->toDateString();
            $currentPlus24 = $currentDateTime->copy()->addDay();

            if ($requestedDate < $currentPlus24->toDateString()) {
                return $this->responseWithError('Service should be booked at least 24 hours in advance.', 422);
            }

            // Calculate day start time based on booking rules
            if ($requestedDate === $currentPlus24->toDateString()) {
                $businessEndDateTime = Carbon::parse($requestedDate . ' ' . $businessTiming->end_time);
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
            } else {
                $dayStartTime = Carbon::parse($requestedDate . ' ' . $businessTiming->start_time);
            }

            $dayEndTime = Carbon::parse($requestedDate . ' ' . $businessTiming->end_time);
            $totalMinutes = $area_of_land * $equipmentType->minutes_per_kanal;
            $bufferMinutes = 30;

            // Get available partners and their capacity for this area and equipment type
            $availableCapacity = $this->getAvailableCapacityForSlot($areaId, $equipmentTypeId, $date);

            if ($availableCapacity === 0) {
                return $this->responseWithError('No partners available for this service in the selected area.', 422);
            }

            // Get existing bookings for the date
            $existingBookings = $this->getExistingBookings($requestedDate, $areaId, $equipmentTypeId, $bufferMinutes);

            // Generate available slots
            $slots = $this->generateAvailableSlots(
                $dayStartTime,
                $dayEndTime,
                $totalMinutes,
                $existingBookings,
                $availableCapacity,
                $areaId,
                $equipmentTypeId
            );

            if (empty($slots)) {
                return $this->responseWithError('No slots available for the selected date.', 422);
            }

            return $this->responseWithSuccess($slots, 'Available slots fetched successfully', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Get available capacity (number of simultaneous bookings possible) for given parameters
     */
    private function getAvailableCapacityForSlot($areaId, $equipmentTypeId, $date)
    {
        $equipmentType = EquipmentType::find($equipmentTypeId);
        $dateStart = $date->startOfDay();
        $dateEnd = $date->endOfDay();
        // dd($dateStart, $dateEnd);
        // Get partners who cover this area and have this equipment type
        $partnersQuery = Partner::whereHas('coverages', function ($query) use ($areaId) {
            $query->where('area_id', $areaId)->where('is_enabled', true);
        })->whereHas('units', function ($query) use ($equipmentTypeId) {
            $query->where('equipment_type_id', $equipmentTypeId)
                ->where('status', 'active');
        });

        // Filter out unavailable partners
        $partnersQuery->whereDoesntHave('unavailability', function ($query) use ($dateStart, $dateEnd) {
            $query->where(function ($q) use ($dateStart, $dateEnd) {
                $q->where('start_at', '<=', $dateEnd)
                    ->where('end_at', '>=', $dateStart);
            });
        });

        $availablePartners = $partnersQuery->with([
            'drivers' => function ($query) use ($dateStart, $dateEnd) {
                $query->where('status', 'active')
                    ->whereDoesntHave('unavailability', function ($q) use ($dateStart, $dateEnd) {
                        $q->where('start_at', '<=', $dateEnd)
                            ->where('end_at', '>=', $dateStart);
                    });
            },
            'tractors' => function ($query) use ($dateStart, $dateEnd) {
                $query->where('status', 'active')
                    ->whereDoesntHave('unavailability', function ($q) use ($dateStart, $dateEnd) {
                        $q->where('start_at', '<=', $dateEnd)
                            ->where('end_at', '>=', $dateStart);
                    });
            },
            'units' => function ($query) use ($equipmentTypeId, $dateStart, $dateEnd) {
                $query->where('equipment_type_id', $equipmentTypeId)
                    ->where('status', 'active')
                    ->whereDoesntHave('unavailability', function ($q) use ($dateStart, $dateEnd) {
                        $q->where('start_at', '<=', $dateEnd)
                            ->where('end_at', '>=', $dateStart);
                    });
            }
        ])->get();
        $totalCapacity = 0;

        foreach ($availablePartners as $partner) {
            $partnerCapacity = 0;
            $availableDrivers = $partner->drivers->count();
            $availableEquipment = $partner->units->count();

            if ($equipmentType->is_self_propelled) {
                // For self-propelled equipment: capacity = min(drivers, equipment)
                $partnerCapacity = min($availableDrivers, $availableEquipment);
            } else if ($equipmentType->requires_tractor) {
                // For tractor-required equipment: capacity = min(drivers, tractors, equipment)
                $availableTractors = $partner->tractors->count();
                $partnerCapacity = min($availableDrivers, $availableTractors, $availableEquipment);
            } else {
                // For equipment that doesn't require tractor: capacity = min(drivers, equipment)
                $partnerCapacity = min($availableDrivers, $availableEquipment);
            }

            $totalCapacity += $partnerCapacity;
        }
        // dd($totalCapacity);
        return $totalCapacity;
    }

    /**
     * Get existing bookings for the date with buffer time
     */
    private function getExistingBookings($requestedDate, $areaId, $equipmentTypeId, $bufferMinutes)
    {
        $bookings = Booking::withoutGlobalScopes()->where('slot_date', $requestedDate)
            ->where('area_id', $areaId)
            ->where('equipment_type_id', $equipmentTypeId)
            ->where(function ($q) {
                $q->where('payment_status', 'confirmed')
                    ->orWhere(function ($q2) {
                        $q2->where('payment_status', 'pending')
                            ->where('reserved_until', '>', now());
                    });
            })
            ->orderBy('start_time')
            ->get()
            ->map(function ($booking) use ($requestedDate, $bufferMinutes) {
                // Parse the time strings and create full datetime objects for comparison
                $startTime = Carbon::parse($requestedDate . ' ' . $booking->start_time);
                $endTime = Carbon::parse($requestedDate . ' ' . $booking->end_time);

                // Apply buffer time
                $booking->buffered_start_time = $startTime->subMinutes($bufferMinutes);
                $booking->buffered_end_time = $endTime->addMinutes($bufferMinutes);

                return $booking;
            });
            // dd($bookings);
        return $bookings;
    }

    /**
     * Generate available time slots based on capacity and existing bookings
     */
    /**
     * Generate available time slots based on capacity and existing bookings
     */
    private function generateAvailableSlots($dayStartTime, $dayEndTime, $totalMinutes, $existingBookings, $availableCapacity, $areaId, $equipmentTypeId)
    {
        $slots = [];
        $currentStart = $dayStartTime->copy();

        while ($currentStart->lt($dayEndTime)) {
            $slotEnd = $currentStart->copy()->addMinutes($totalMinutes);

            if ($slotEnd->gt($dayEndTime)) {
                break;
            }

            // Count overlapping bookings for this time slot
            $overlappingCount = $existingBookings->filter(function ($booking) use ($currentStart, $slotEnd) {
                // Use the buffered times for overlap checking
                return $slotEnd->gt($booking->buffered_start_time) && $currentStart->lt($booking->buffered_end_time);
            })->count();

            // If we have available capacity (total capacity - overlapping bookings > 0)
            if ($overlappingCount < $availableCapacity) {
                $slots[] = [
                    'start_time' => $currentStart->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'duration_required_minutes' => $totalMinutes,
                    'area_id' => $areaId,
                    'equipment_type_id' => $equipmentTypeId,
                    'available_capacity' => $availableCapacity - $overlappingCount
                ];
            }

            // Move to next slot (you can adjust the increment as needed, e.g., 30 minutes)
            $currentStart->addMinutes(30);
        }

        return $slots;
    }
}
