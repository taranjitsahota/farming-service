<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BusinessTiming;
use App\Models\EquipmentType;
use App\Models\Partner;
use App\Models\PartnerAreaCoverage;
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
            // dd($availableDrivers, $availableEquipment);
            // if ($equipmentType->is_self_propelled) {
            //     // For self-propelled equipment: capacity = min(drivers, equipment)
            //     $partnerCapacity = min($availableDrivers, $availableEquipment);
           if ($equipmentType->requires_tractor) {
                // For tractor-required equipment: capacity = min(drivers, tractors, equipment)
                $availableTractors = $partner->tractors->count();
                // dd($availableTractors, $availableDrivers, $availableEquipment);
                $partnerCapacity = min($availableDrivers, $availableTractors, $availableEquipment);
            } else {
                // For equipment that doesn't require tractor: capacity = min(drivers, equipment)
                $partnerCapacity = min($availableDrivers, $availableEquipment);
            }

            $totalCapacity += $partnerCapacity;
        }
        // dd($areaId, $equipmentTypeId, $dateStart, $dateEnd);
        // $bookingsCount = Booking::withoutGlobalScopes()->where('area_id', $areaId)
        //     ->where('equipment_type_id', $equipmentTypeId) // or equipment_type_id
        //     ->where('start_time', '<=', $dateEnd)
        //     ->where('end_time', '>=', $dateStart)
        //     ->count();
        // dd($bookingsCount);
        // $capacity = max(0, $totalCapacity - $bookingsCount);
        // dd($capacity);
        // return $capacity;
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
    // private function generateAvailableSlots($dayStartTime, $dayEndTime, $totalMinutes, $existingBookings, $availableCapacity, $areaId, $equipmentTypeId)
    // {
    //     $slots = [];
    //     $currentStart = $dayStartTime->copy();

    //     while ($currentStart->lt($dayEndTime)) {
    //         $slotEnd = $currentStart->copy()->addMinutes($totalMinutes);

    //         if ($slotEnd->gt($dayEndTime)) {
    //             break;
    //         }

    //         // Count overlapping bookings for this time slot
    //         $overlappingCount = $existingBookings->filter(function ($booking) use ($currentStart, $slotEnd) {
    //             // Use the buffered times for overlap checking
    //             return $slotEnd->gt($booking->buffered_start_time) && $currentStart->lt($booking->buffered_end_time);
    //         })->count();

    //         $partners = PartnerAreaCoverage::with(['partner.drivers', 'partner.tractors', 'partner.units'])
    //             ->enabled()
    //             ->where('area_id', $areaId)
    //             ->get()
    //             ->pluck('partner'); // just take partners

    //         // Step 2: Count total drivers for these partners
    //         $totalDrivers = $partners->flatMap->drivers
    //             ->where('status', 'active')
    //             ->count();

    //         // Step 3: Count total tractors
    //         $totalTractors = $partners->flatMap->tractors
    //             ->where('status', 'active')
    //             ->count();

    //         // Step 4: Count total equipments
    //         $totalEquipments = $partners->flatMap->units
    //             ->where('status', 'active')
    //             ->count();

    //         // Step 3: effective capacity = min(drivers, tractors, equipments)
    //         $totalCapacity = min($totalDrivers, $totalTractors, $totalEquipments);

    //         // If we have available capacity (total capacity - overlapping bookings > 0)
    //         // if ($overlappingCount < $availableCapacity) {
    //         if ($overlappingCount < $totalCapacity) {
    //             $slots[] = [
    //                 'start_time' => $currentStart->format('H:i'),
    //                 'end_time' => $slotEnd->format('H:i'),
    //                 'duration_required_minutes' => $totalMinutes,
    //                 'area_id' => $areaId,
    //                 'equipment_type_id' => $equipmentTypeId,
    //                 'available_capacity' => $availableCapacity - $overlappingCount
    //             ];
    //         }

    //         // Move to next slot (you can adjust the increment as needed, e.g., 30 minutes)
    //         $currentStart->addMinutes(30);
    //     }

    //     return $slots;
    // }



    // private function generateAvailableSlots($dayStartTime, $dayEndTime, $totalMinutes, $existingBookings, $availableCapacity, $areaId, $equipmentTypeId)
    // {
    //     $slots = [];
    //     $currentStart = $dayStartTime->copy();

    //     // Get equipment type
    //     $equipmentType = EquipmentType::findOrFail($equipmentTypeId);

    //     // Preload partners once for efficiency
    //     $partners = PartnerAreaCoverage::with(['partner.tractors', 'partner.units'])
    //         ->enabled()
    //         ->where('area_id', $areaId)
    //         ->get()
    //         ->pluck('partner');

    //     // Count tractors (active only)
    //     $totalTractors = $partners->flatMap->tractors
    //         ->where('status', 'active')
    //         ->count();
    //     // dd($totalTractors);
    //     // Count drivers (active only)
    //     $totalDrivers = $partners->flatMap->drivers
    //         ->where('status', 'active')
    //         ->count();
    //     // dd($totalDrivers);
    //     // Count equipments (active only for this type)
    //     $totalEquipments = $partners->flatMap->units
    //         ->where('equipment_type_id', $equipmentTypeId)
    //         ->where('status', 'active')
    //         ->count();
    //     // dd($totalDrivers, $totalTractors, $totalEquipments);
    //     // Final capacity logic
    //     // if ($equipmentType->requires_tractor) {
    //     //     $totalCapacity = min($totalDrivers, $totalTractors, $totalEquipments);
    //     //     // dd($totalCapacity);
    //     // } else {
    //     //     $totalCapacity = min($totalDrivers, $totalEquipments);
    //     // }
    //     // dd($totalCapacity);
    //     // Loop over slots
    //     while ($currentStart->lt($dayEndTime)) {
    //         $slotEnd = $currentStart->copy()->addMinutes($totalMinutes);

    //         if ($slotEnd->gt($dayEndTime)) {
    //             break;
    //         }

    //         // Overlapping bookings
    //         $overlappingCount = $existingBookings->filter(function ($booking) use ($currentStart, $slotEnd) {
    //             return $slotEnd->gt($booking->buffered_start_time) && $currentStart->lt($booking->buffered_end_time);
    //         });

    //         $usedDrivers   = $overlappingCount->count(); // each booking uses 1 driver
    //         $usedEquipments = $overlappingCount->count(); // each booking uses 1 equipment
    //         $usedTractors  = $equipmentType->requires_tractor ? $overlappingCount->count() : 0;

    //         // ✅ remaining resources
    //         $availableDrivers   = max(0, $totalDrivers - $usedDrivers);
    //         $availableEquipments = max(0, $totalEquipments - $usedEquipments);
    //         $availableTractors  = $equipmentType->requires_tractor
    //             ? max(0, $totalTractors - $usedTractors)
    //             : PHP_INT_MAX; // if not required, tractor is unlimited

    //         // ✅ effective capacity = min of available pools
    //         $effectiveCapacity = $equipmentType->requires_tractor
    //             ? min($availableDrivers, $availableEquipments, $availableTractors)
    //             : min($availableDrivers, $availableEquipments);

    //         // dd($overlappingCount, $totalCapacity);
    //         if ($effectiveCapacity > 0) {
    //             $slots[] = [
    //                 'start_time' => $currentStart->format('H:i'),
    //                 'end_time' => $slotEnd->format('H:i'),
    //                 'duration_required_minutes' => $totalMinutes,
    //                 'area_id' => $areaId,
    //                 'equipment_type_id' => $equipmentTypeId,
    //                 // 'available_capacity' => $totalCapacity - $overlappingCount
    //             ];
    //         }

    //         $currentStart->addMinutes(30); // move to next slot
    //     }

    //     return $slots;
    // }

    // private function generateAvailableSlots(
    //     $dayStartTime,
    //     $dayEndTime,
    //     $totalMinutes,
    //     $existingBookings,   // all bookings in this area for the day
    //     $availableCapacity,
    //     $areaId,
    //     $equipmentTypeId
    // ) {
    //     $slots = [];
    //     $currentStart = $dayStartTime->copy();

    //     // ✅ fetch equipment type once
    //     $equipmentType = EquipmentType::findOrFail($equipmentTypeId);

    //     // ✅ load partners and their resources
    //     $partners = PartnerAreaCoverage::with(['partner.drivers', 'partner.tractors', 'partner.units'])
    //         ->enabled()
    //         ->where('area_id', $areaId)
    //         ->get()
    //         ->pluck('partner');
    //     // dd($partners);
    //     $totalDrivers    = $partners->flatMap->drivers->where('status', 'active')->count();
    //     $totalTractors   = $partners->flatMap->tractors->where('status', 'active')->count();
    //     $totalEquipments = $partners->flatMap->units
    //         ->where('status', 'active')
    //         ->where('equipment_type_id', $equipmentTypeId)
    //         ->count();

    //     while ($currentStart->lt($dayEndTime)) {
    //         $slotEnd = $currentStart->copy()->addMinutes($totalMinutes);
    //         if ($slotEnd->gt($dayEndTime)) break;

    //         // ✅ all overlapping bookings in this area
    //         $overlappingAreaBookings = $existingBookings->filter(function ($booking) use ($currentStart, $slotEnd, $areaId) {
    //             return $booking->area_id == $areaId &&
    //                 $slotEnd->gt($booking->buffered_start_time) &&
    //                 $currentStart->lt($booking->buffered_end_time);
    //         });

    //         // ✅ overlapping bookings for this equipment type
    //         $overlappingEquipmentBookings = $overlappingAreaBookings->filter(function ($booking) use ($equipmentTypeId) {
    //             return $booking->equipment_type_id == $equipmentTypeId;
    //         });

    //         // 🔹 Drivers → always needed (all bookings consume one)
    //         $usedDrivers = $overlappingAreaBookings->count();

    //         // 🔹 Equipments → only this type
    //         $usedEquipments = $overlappingEquipmentBookings->count();

    //         // 🔹 Tractors → only bookings where equipment.requires_tractor
    //         $usedTractors = $overlappingAreaBookings->filter(function ($booking) {
    //             return $booking->equipmentType && $booking->equipmentType->requires_tractor;
    //         })->count();

    //         // ✅ available pools
    //         $availableDrivers   = max(0, $totalDrivers - $usedDrivers);
    //         $availableEquipments = max(0, $totalEquipments - $usedEquipments);
    //         $availableTractors  = $equipmentType->requires_tractor
    //             ? max(0, $totalTractors - $usedTractors)
    //             : PHP_INT_MAX; // not limiting if not required

    //         // ✅ final slot capacity
    //         $effectiveCapacity = $equipmentType->requires_tractor
    //             ? min($availableDrivers, $availableEquipments, $availableTractors)
    //             : min($availableDrivers, $availableEquipments);
    //         // dd($effectiveCapacity);
    //         if ($effectiveCapacity > 0) {
    //             $slots[] = [
    //                 'start_time' => $currentStart->format('H:i'),
    //                 'end_time'   => $slotEnd->format('H:i'),
    //                 'duration_required_minutes' => $totalMinutes,
    //                 'area_id' => $areaId,
    //                 'equipment_type_id' => $equipmentTypeId,
    //                 'available_capacity' => $effectiveCapacity
    //             ];
    //         }

    //         $currentStart->addMinutes(30);
    //     }
    //     Log::info("Slot check", [
    //         'start' => $currentStart->format('H:i'),
    //         'end'   => $slotEnd->format('H:i'),
    //         'totalDrivers' => $totalDrivers,
    //         'usedDrivers'  => $usedDrivers,
    //         'totalEquipments' => $totalEquipments,
    //         'usedEquipments'  => $usedEquipments,
    //         'totalTractors'   => $totalTractors,
    //         'usedTractors'    => $usedTractors,
    //         'effectiveCapacity' => $effectiveCapacity
    //     ]);

    //     return $slots;
    // }

    // private function generateAvailableSlots(
    //     $dayStartTime,
    //     $dayEndTime,
    //     $totalMinutes,
    //     $existingBookings,      // same-type bookings (already buffered) for this date/area
    //     $availableCapacity,     // not used anymore; kept for signature compatibility
    //     $areaId,
    //     $equipmentTypeId
    // ) {
    //     $slots = [];
    //     $requestedDate = $dayStartTime->toDateString();

    //     // Selected equipment type (to know if tractor needed for *new* booking)
    //     $equipmentType = EquipmentType::findOrFail($equipmentTypeId);

    //     // 1) Total resource pools in this area (active only)
    //     $partners = PartnerAreaCoverage::with(['partner.drivers', 'partner.tractors', 'partner.units'])
    //         ->enabled()
    //         ->where('area_id', $areaId)
    //         ->get()
    //         ->pluck('partner');

    //     $totalDrivers = $partners->flatMap->drivers
    //         ->where('status', 'active')
    //         ->count();

    //     $totalTractors = $partners->flatMap->tractors
    //         ->where('status', 'active')
    //         ->count();

    //     $totalEquipments = $partners->flatMap->units
    //         ->where('status', 'active')
    //         ->where('equipment_type_id', $equipmentTypeId)
    //         ->count();

    //     // 2) Area-wide bookings (all equipment types) for driver/tractor usage
    //     $bufferMinutes = 30; // keep in sync with the caller
    //     $areaBookingsAllTypes = Booking::withoutGlobalScopes()
    //         ->where('slot_date', $requestedDate)
    //         ->where('area_id', $areaId)
    //         ->where(function ($q) {
    //             $q->where('payment_status', 'confirmed')
    //                 ->orWhere(function ($q2) {
    //                     $q2->where('payment_status', 'pending')
    //                         ->where('reserved_until', '>', now());
    //                 });
    //         })
    //         ->with('equipmentType:id,requires_tractor') // to know if the booking consumed a tractor
    //         ->orderBy('start_time')
    //         ->get()
    //         ->map(function ($b) use ($requestedDate, $bufferMinutes) {
    //             $start = Carbon::parse($requestedDate . ' ' . $b->start_time);
    //             $end   = Carbon::parse($requestedDate . ' ' . $b->end_time);
    //             $b->buffered_start_time = $start->copy()->subMinutes($bufferMinutes);
    //             $b->buffered_end_time   = $end->copy()->addMinutes($bufferMinutes);
    //             return $b;
    //         });

    //     // 3) Build slots with per-slot capacity
    //     $currentStart = $dayStartTime->copy();

    //     while ($currentStart->lt($dayEndTime)) {
    //         $slotEnd = $currentStart->copy()->addMinutes($totalMinutes);
    //         if ($slotEnd->gt($dayEndTime)) {
    //             break;
    //         }

    //         // Same-type overlaps → consume *equipments* of the selected type
    //         $overlapSameType = $existingBookings->filter(function ($b) use ($currentStart, $slotEnd) {
    //             return $slotEnd->gt($b->buffered_start_time) && $currentStart->lt($b->buffered_end_time);
    //         });
    //         $usedEquipments = $overlapSameType->count();

    //         // Area-wide overlaps → consume *drivers* and (conditionally) *tractors*
    //         $overlapArea = $areaBookingsAllTypes->filter(function ($b) use ($currentStart, $slotEnd) {
    //             return $slotEnd->gt($b->buffered_start_time) && $currentStart->lt($b->buffered_end_time);
    //         });
    //         $usedDrivers  = $overlapArea->count();
    //         $usedTractors = $overlapArea->filter(function ($b) {
    //             return $b->equipmentType && $b->equipmentType->requires_tractor;
    //         })->count();

    //         // Remaining pool for this particular slot
    //         $availableDrivers    = max(0, $totalDrivers    - $usedDrivers);
    //         $availableEquipments = max(0, $totalEquipments - $usedEquipments);
    //         $availableTractors   = max(0, $totalTractors   - $usedTractors);

    //         // Capacity for *this* slot (drivers are always required)
    //         $slotCapacity = $equipmentType->requires_tractor
    //             ? min($availableDrivers, $availableEquipments, $availableTractors)
    //             : min($availableDrivers, $availableEquipments);

    //         if ($slotCapacity > 0) {
    //             $slots[] = [
    //                 'start_time'                 => $currentStart->format('H:i'),
    //                 'end_time'                   => $slotEnd->format('H:i'),
    //                 'duration_required_minutes'  => $totalMinutes,
    //                 'area_id'                    => $areaId,
    //                 'equipment_type_id'          => $equipmentTypeId,
    //                 'available_capacity'         => $slotCapacity,
    //             ];
    //         }

    //         $currentStart->addMinutes(30);
    //     }

    //     return $slots;
    // }




















    private function generateAvailableSlots(
        $dayStartTime,
        $dayEndTime,
        $totalMinutes,
        $existingBookings,      // same-type bookings (already buffered) for this date/area
        $availableCapacity,     // not used now, kept for compatibility
        $areaId,
        $equipmentTypeId
    ) {
        $slots = [];
        $requestedDate = $dayStartTime->toDateString();

        $equipmentType = EquipmentType::findOrFail($equipmentTypeId);

        // Preload partners and resources and their unavailabilities (one DB call)
        $partners = PartnerAreaCoverage::with([
            'partner.drivers.unavailability',
            'partner.tractors.unavailability',
            'partner.units.unavailability',
            'partner.unavailability'
        ])
            ->enabled()
            ->where('area_id', $areaId)
            ->get()
            ->pluck('partner');

        // dd(
        //     $partners->flatMap(function ($partner) {
        //         return $partner->drivers->flatMap->unavailability;
        //     })
        // );


        // Also load all bookings in the area (all equipment types) for the date,
        // so we can compute drivers/tractor usage per slot.
        $bufferMinutes = 30;
        $areaBookingsAllTypes = Booking::withoutGlobalScopes()
            ->where('slot_date', $requestedDate)
            ->where('area_id', $areaId)
            ->where(function ($q) {
                $q->where('payment_status', 'confirmed')
                    ->orWhere(function ($q2) {
                        $q2->where('payment_status', 'pending')
                            ->where('reserved_until', '>', now());
                    });
            })
            ->with('equipmentType:id,requires_tractor')
            ->orderBy('start_time')
            ->get()
            ->map(function ($b) use ($requestedDate, $bufferMinutes) {
                $bStart = Carbon::parse($requestedDate . ' ' . $b->start_time);
                $bEnd   = Carbon::parse($requestedDate . ' ' . $b->end_time);
                $b->buffered_start_time = $bStart->copy()->subMinutes($bufferMinutes);
                $b->buffered_end_time   = $bEnd->copy()->addMinutes($bufferMinutes);
                return $b;
            });

        $currentStart = $dayStartTime->copy();

        // Helper closure: does any unavailability collection overlap this slot?
        $overlapsWindow = function ($unavCollection, Carbon $slotStart, Carbon $slotEnd) {
            foreach ($unavCollection as $u) {
                // ensure we have Carbon instances
                $uStart = Carbon::parse($u->start_at);
                $uEnd   = Carbon::parse($u->end_at);
                if ($slotEnd->gt($uStart) && $slotStart->lt($uEnd)) {
                    return true;
                }
            }
            return false;
        };

        while ($currentStart->lt($dayEndTime)) {
            $slotEnd = $currentStart->copy()->addMinutes($totalMinutes);
            if ($slotEnd->gt($dayEndTime)) break;

            // 1) Compute total available pools for THIS slot (respecting unavailability)
            $totalDrivers = 0;
            $totalTractors = 0;
            $totalEquipments = 0;

            foreach ($partners as $partner) {
                // skip partner if partner_unavailability overlaps this slot
                if (isset($partner->unavailability) && $overlapsWindow($partner->unavailability, $currentStart, $slotEnd)) {
                    continue;
                }

                // drivers for this partner available in slot
                foreach ($partner->drivers as $driver) {
                    // dd($driver);
                    if ($driver->status !== 'active') continue;
                    // skip driver if driver_unavailability overlaps this slot
                    // dd($overlapsWindow, $driver->unavailability, $currentStart, $slotEnd);
                    if (isset($driver->unavailability) && $overlapsWindow($driver->unavailability, $currentStart, $slotEnd)) {
                        // dd($driver->unavailability, $currentStart, $slotEnd);
                        continue;
                    }
                    // dd($driver);
                    $totalDrivers++;
                }

                // tractors for this partner available in slot
                foreach ($partner->tractors as $tractor) {
                    if ($tractor->status !== 'active') continue;
                    if (isset($tractor->unavailability) && $overlapsWindow($tractor->unavailability, $currentStart, $slotEnd)) {
                        continue;
                    }
                    $totalTractors++;
                }

                // units (equipments) of requested type available in slot for this partner
                foreach ($partner->units as $unit) {
                    if ($unit->status !== 'active') continue;
                    if ((int)$unit->equipment_type_id !== (int)$equipmentTypeId) continue;
                    if (isset($unit->unavailability) && $overlapsWindow($unit->unavailability, $currentStart, $slotEnd)) {
                        continue;
                    }
                    $totalEquipments++;
                }
            }
            // dd($totalDrivers);
            // 2) Count how many overlapping bookings consume resources in THIS slot
            // - usedDrivers: count of all overlapping bookings in area (any equipment type)
            // - usedEquipments: count of overlapping bookings of this equipment type
            // - usedTractors: count of overlapping bookings whose equipmentType.requires_tractor === true
            $overlapArea = $areaBookingsAllTypes->filter(function ($b) use ($currentStart, $slotEnd) {
                return $slotEnd->gt($b->buffered_start_time) && $currentStart->lt($b->buffered_end_time);
            });

            $usedDrivers = $overlapArea->count();

            // equipments of this type that overlap (we must only subtract same-type equipment)
            $overlapSameType = $existingBookings->filter(function ($b) use ($currentStart, $slotEnd) {
                return $slotEnd->gt($b->buffered_start_time) && $currentStart->lt($b->buffered_end_time);
            });
            $usedEquipments = $overlapSameType->count();

            // tractors used by overlapping bookings (bookings whose equipment requires tractor)
            $usedTractors = $overlapArea->filter(function ($b) {
                return isset($b->equipmentType) && $b->equipmentType->requires_tractor;
            })->count();

            // 3) Remaining pools
            $availableDrivers    = max(0, $totalDrivers - $usedDrivers);
            $availableEquipments = max(0, $totalEquipments - $usedEquipments);
            $availableTractors   = max(0, $totalTractors - $usedTractors);

            // 4) slot capacity depending on requires_tractor
            if ($equipmentType->requires_tractor) {
                $slotCapacity = min($availableDrivers, $availableEquipments, $availableTractors);
            } else {
                $slotCapacity = min($availableDrivers, $availableEquipments);
            }

            // debug log (optional during testing)
            // \Log::info('Slot check', [
            //     'slot_start' => $currentStart->format('H:i'),
            //     'slot_end'   => $slotEnd->format('H:i'),
            //     'totalDrivers' => $totalDrivers, 'usedDrivers' => $usedDrivers,
            //     'totalEquipments' => $totalEquipments, 'usedEquipments' => $usedEquipments,
            //     'totalTractors' => $totalTractors, 'usedTractors' => $usedTractors,
            //     'slotCapacity' => $slotCapacity
            // ]);

            if ($slotCapacity > 0) {
                $slots[] = [
                    'start_time' => $currentStart->format('H:i'),
                    'end_time'   => $slotEnd->format('H:i'),
                    'duration_required_minutes' => $totalMinutes,
                    'area_id' => $areaId,
                    'equipment_type_id' => $equipmentTypeId,
                    'available_capacity' => $slotCapacity
                ];
            }

            $currentStart->addMinutes(30);
        }

        return $slots;
    }
}
