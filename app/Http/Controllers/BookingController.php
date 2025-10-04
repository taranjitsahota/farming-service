<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\EquipmentType;
use App\Models\Partner;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Traits\Subscriptions\isSubscribed;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{

    use isSubscribed;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $bookings = Booking::where('user_id', $user->id)
                ->with(['user', 'crop'])
                ->get();

            return $this->responseWithSuccess($bookings, 'Bookings fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
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
        $bookings = Booking::find($id)->with('service')->with('user')->with('crop')->with('service')->with('servicearea');
        return $this->responseWithSuccess($bookings, 'Bookings fetched successfully', 200);
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
    public function destroy(string $id) {}

    public function getAllBookings()
    {
        try {
            $bookings = Booking::with('user')->with('crop')->with('equipmentType')->get();

            $formatted = $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'user_name' => $booking->user->name,
                    'phone' => $booking->user->phone,
                    'address' => $booking->address,
                    'equipment' => $booking->equipmentType->equipment->name,
                    'crop_id' => $booking->crop_id,
                    'crop_name' => $booking->crop->name,
                    'equipment_name' => $booking->equipmentType->equipment->name,
                    // 'servicearea_id' => $booking->servicearea_id,
                    // 'servicearea_name' => $booking->servicearea->name,
                    'date' => $booking->slot_date->format('Y-m-d'),
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'payment_status' => $booking->payment_status,
                    'booking_status' => $booking->booking_status,
                    'amount' => $booking->price,
                    'duration' => $booking->duration_minutes,
                    'area' => $booking->area,
                    'created_at' => $booking->created_at->format('Y-m-d'),


                ];
            });


            return $this->responseWithSuccess($formatted, 'Bookings fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    public function getPendingBookings()
    {
        try {
            $bookings = Booking::where('booking_status', 'pending')->with('user', 'equipmentType', 'crop', 'area')->get();

            $formatted = $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'user_name' => $booking->user->name,
                    'phone' => $booking->user->phone,
                    'email' => $booking->user->email ?? '',
                    'address' => $booking->address,
                    'land_area' => $booking->land_area,
                    'equipment_name' => $booking->equipmentType->equipment->name,
                    'user_note' => $booking->user_note,
                    'crop_id' => $booking->crop_id,
                    'crop_name' => $booking->crop->name,
                    'area' => $booking->area->village->name,
                    // 'equipment_name' => $booking->servicearea->equipment->name,
                    'service_name' => $booking->equipmentType->service->name,
                    'date' => $booking->slot_date->format('Y-m-d'),
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'payment_status' => $booking->payment_status,
                    'booking_status' => $booking->booking_status,
                    'amount' => $booking->price,
                    'duration' => $booking->duration_minutes,
                    'substation' => $booking->substation->name,
                    'payment_method' => $booking->payment_method,
                    'created_at' => $booking->created_at->format('Y-m-d'),
                    'paid_at' => $booking->paid_at->format('Y-m-d'),
                    'partner_id' => $booking->partner_id,
                    'driver_id' => $booking->driver_id,
                    'tractor_id' => $booking->tractor_id,
                    'equipment_unit_id' => $booking->equipment_unit_id,
                    'admin_note' => $booking->admin_note,
                    'lattitude' => $booking->latitude,
                    'longitude' => $booking->longitude
                ];
            });

            return $this->responseWithSuccess($formatted, 'Bookings fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }



    public function getEstimatedPayment(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'area' => 'required|numeric|min:1'
        ]);

        $service = Service::with('equipment')->findOrFail($request->service_id);
        $equipment = $service->equipment;
        // dd($equipment);
        $area = $request->area;
        // Check minimum area allowed
        if ($equipment->min_kanal && $area < $equipment->min_kanal) {
            return $this->responseWithError('Minimum area in kanals is ' . $equipment->min_kanal . ' kanals', 422);
        }
        $rate_per_kanal = $equipment->price_per_kanal;
        $estimated_amount = $area * $rate_per_kanal;

        return $this->responseWithSuccess([
            'area_selected' => $area,
            'price_per_kanal' => $rate_per_kanal,
            'total_estimated_amount' => $estimated_amount,
            'currency' => 'INR'
        ], 'Estimated payment calculated successfully');
    }

    // public function bookSlot(Request $request)
    // {
    //     try {
    //         DB::beginTransaction();
    //         $request->validate([
    //             'slot_date' => 'required|date',
    //             'start_time' => 'required',
    //             'area' => 'required|numeric|min:1',
    //             'crop_id' => 'required|exists:crops,id',
    //             'service_area_id' => 'required|exists:serviceareas,id',
    //             'substation_id' => 'required|exists:substations,id'
    //         ]);

    //         $user = auth()->user();

    //         $serviceArea = ServiceArea::findOrFail($request->service_area_id);
    //         $equipment = $serviceArea->equipment;

    //         if ($equipment->min_kanal && $request->area < $equipment->min_kanal) {
    //             return $this->responseWithError('Minimum area is ' . $equipment->min_kanal . ' kanals', 422);
    //         }

    //         $alreadyPending = Booking::where('user_id', $user->id)
    //             ->where('service_area_id', $request->service_area_id)
    //             ->where('substation_id', $request->substation_id)
    //             ->where('payment_status', 'pending')
    //             ->where('reserved_until', '>', now())
    //             ->exists();

    //         if ($alreadyPending) {
    //             return $this->responseWithError('You already have a pending booking. Please complete payment. Or cancel the wait for 5 minutes.', 422);
    //         }

    //         $conflictBooking = Booking::where('service_area_id', $request->service_area_id)
    //             ->where('substation_id', $request->substation_id)
    //             ->where('slot_date', $request->slot_date)
    //             ->where(function ($q) use ($request) {
    //                 $q->whereBetween('start_time', [$request->start_time, $request->end_time])
    //                     ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
    //             })
    //             ->exists();

    //         if ($conflictBooking) {
    //             return $this->responseWithError('This Slot is already Booked. Please choose another slot.', 422);
    //         };

    //         $price = $equipment->price_per_kanal * $request->area;
    //         $area = $request->area;
    //         $duration = $area * $equipment->minutes_per_kanal;
    //         $start = Carbon::parse($request->slot_date . ' ' . $request->start_time);
    //         $end = $start->copy()->addMinutes($duration);

    //         // Check conflict with confirmed & pending bookings
    //         // $conflict = Booking::where('slot_date', $request->slot_date)
    //         //     ->where(function ($q) use ($start, $end) {
    //         //         $q->whereBetween('start_time', [$start, $end])
    //         //             ->orWhereBetween('end_time', [$start, $end]);
    //         //     })
    //         //     ->where(function ($q) {
    //         //         $q->where('status', 'confirmed')
    //         //             ->orWhere(function ($q2) {
    //         //                 $q2->where('status', 'pending')
    //         //                     ->where('reserved_until', '>', now());
    //         //             });
    //         //     })
    //         //     ->exists();


    //         // if ($conflict) {
    //         //     return $this->responseWithError('Selected slot is already booked or reserved', 422);
    //         // }

    //         // Reserve slot with status 'pending'
    //         // $booking = Booking::create([
    //         //     'user_id' => $user->id,
    //         //     'service_id' => $request->service_id,
    //         //     'crop_id' => $request->crop_id,
    //         //     'area_id' => $request->area_id,
    //         //     'price' => $price,
    //         //     'slot_date' => $request->slot_date,
    //         //     'start_time' => $start,
    //         //     'end_time' => $end,
    //         //     'duration' => $duration,
    //         //     'service_area_id' => $area,
    //         //     'status' => 'pending',
    //         //     'reserved_until' => now()->addMinutes(15)
    //         // ]);

    //         // return $this->responseWithSuccess(['booking_id' => $booking->id], 'Slot reserved. Proceed to payment.');

    //         $subscribed = $this->isSubscribed($user, $request->service_id, $request->area);

    //         $status = $subscribed ? 'confirmed' : 'pending';
    //         $reservedUntil = $subscribed ? null : now()->addMinutes(15);

    //         $booking = Booking::create([
    //             'user_id' => $user->id,
    //             'substation_id' => $request->substation_id,
    //             'crop_id' => $request->crop_id,
    //             'area_id' => $request->area_id,
    //             'land_area' =>  $area,
    //             'price' => $subscribed ? 0 : $price, // No price if subscribed
    //             'slot_date' => $request->slot_date,
    //             'start_time' => $start,
    //             'end_time' => $end,
    //             'duration' => $duration,
    //             'service_area_id' => $request->service_area_id,
    //             'payment_status' => $status,
    //             'reserved_until' => $reservedUntil
    //         ]);

    //         $message = $subscribed
    //             ? 'Slot booked using your subscription. No payment required.'
    //             : 'Slot reserved. Proceed to payment.';

    //         DB::commit();
    //         return $this->responseWithSuccess(['booking_id' => $booking->id], $message);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return $this->responseWithError('Validation failed', 422, $e->errors());
    //     } catch (\Exception $e) {
    //         return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
    //     }
    // }

    public function bookSlot(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'slot_date' => 'required|date',
                'start_time' => 'required',
                'area_of_land' => 'required|numeric|min:1',
                'crop_id' => 'required|exists:crops,id',
                'area_id' => 'required|exists:areas,id',
                'equipment_type_id' => 'required|exists:equipment_types,id',
                'substation_id' => 'required|exists:substations,id',
                'address' => 'nullable|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'user_note' => 'nullable|string'
            ]);

            $user = auth()->user();
            $areaOfLand = $request->area_of_land;
            $areaId = $request->area_id;
            $equipmentTypeId = $request->equipment_type_id;
            $slotDate = $request->slot_date;
            $startTime = $request->start_time;

            if ($slotDate < now()->format('Y-m-d')) {
                return $this->responseWithError('Selected date is in the past', 422);
            }

            // Get equipment type details
            $equipmentType = EquipmentType::findOrFail($equipmentTypeId);

            // Check minimum area requirement
            if ($equipmentType->min_kanal && $areaOfLand < $equipmentType->min_kanal) {
                return $this->responseWithError(
                    'Minimum area required is ' . $equipmentType->min_kanal . ' kanals',
                    422
                );
            }

            // Check if user already has a pending booking for same equipment type and area
            $alreadyPending = Booking::where('user_id', $user->id)
                ->where('area_id', $areaId)
                ->where('equipment_type_id', $equipmentTypeId)
                ->where('payment_status', 'pending')
                ->where('reserved_until', '>', now())
                ->exists();

            if ($alreadyPending) {
                return $this->responseWithError(
                    'You already have a pending booking for this service. Please complete payment or wait for expiration.',
                    422
                );
            }

            // Calculate booking details
            $duration = $areaOfLand * $equipmentType->minutes_per_kanal;
            $price = $equipmentType->price_per_kanal * $areaOfLand;
            $start = Carbon::parse($slotDate . ' ' . $startTime);
            $end = $start->copy()->addMinutes($duration);

            // Check slot availability and get available resources
            // $availableResources = $this->findAvailableResources($areaId, $equipmentTypeId, $start, $end);

            // if (!$availableResources) {
            //     return $this->responseWithError(
            //         'No available resources for this slot. Please choose another time.',
            //         422
            //     );
            // }

            // Check for subscription (if you have subscription logic)
            $subscribed = false; // You can implement subscription check here
            $subscribed = $this->isSubscribed($user, $equipmentType->service_id, $areaOfLand);

            $paymentStatus = $subscribed ? 'confirmed' : 'pending';
            $bookingStatus = $subscribed ? 'pending' : 'pending';
            $reservedUntil = $subscribed ? null : now()->addMinutes(5);
            $finalPrice = $subscribed ? 0 : $price;

            // Create the booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'area_id' => $areaId,
                'equipment_type_id' => $equipmentTypeId,
                // 'partner_id' => $availableResources['partner_id'],
                // 'driver_id' => $availableResources['driver_id'],
                // 'tractor_id' => $availableResources['tractor_id'], // null for self-propelled
                // 'equipment_unit_id' => $availableResources['equipment_unit_id'],
                'substation_id' => $request->substation_id,
                'crop_id' => $request->crop_id,
                'land_area' => $areaOfLand,
                'slot_date' => $slotDate,
                'start_time' => $start->format('H:i'),
                'end_time' => $end->format('H:i'),
                'duration_minutes' => $duration,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'user_note' => $request->user_note,
                'price' => $finalPrice,
                'price_per_kanal' => $equipmentType->price_per_kanal,
                'payment_status' => $paymentStatus,
                'booking_status' => $bookingStatus,
                'reserved_until' => $reservedUntil
            ]);

            $message = $subscribed
                ? 'Slot booked using your subscription. No payment required.'
                : 'Slot reserved successfully. Please complete payment within 15 minutes.';

            DB::commit();

            return $this->responseWithSuccess([
                'booking_id' => $booking->id,
                'booking_details' => [
                    'slot_date' => $booking->slot_date,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'duration_minutes' => $booking->duration_minutes,
                    'land_area' => $booking->land_area,
                    'price' => $booking->price,
                    'payment_status' => $booking->payment_status,
                    'reserved_until' => $booking->reserved_until
                ]
            ], $message);
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Find available resources (partner, driver, tractor, equipment) for the booking
     */
    private function findAvailableResources($areaId, $equipmentTypeId, $startTime, $endTime)
    {
        $equipmentType = EquipmentType::find($equipmentTypeId);
        $dateStart = $startTime->startOfDay();
        $dateEnd = $startTime->endOfDay();

        // Get available partners who cover this area and have this equipment type
        $availablePartners = Partner::whereHas('coverages', function ($query) use ($areaId) {
            $query->where('area_id', $areaId)->where('is_enabled', true);
        })
            ->whereHas('units', function ($query) use ($equipmentTypeId) {
                $query->where('equipment_type_id', $equipmentTypeId)
                    ->where('status', 'active');
            })
            ->whereDoesntHave('unavailability', function ($query) use ($dateStart, $dateEnd) {
                $query->where('start_at', '<=', $dateEnd)
                    ->where('end_at', '>=', $dateStart);
            })
            ->with([
                'drivers' => function ($query) use ($startTime, $endTime) {
                    $query->where('status', 'active')
                        ->whereDoesntHave('bookings', function ($q) use ($startTime, $endTime) {
                            $q->whereDate('slot_date', $startTime->toDateString())
                                ->where('payment_status', 'confirmed')
                                ->where(function ($timeQ) use ($startTime, $endTime) {
                                    $timeQ->where(function ($overlap) use ($startTime, $endTime) {
                                        $overlap->whereTime('start_time', '<', $endTime->format('H:i'))
                                            ->whereTime('end_time', '>', $startTime->format('H:i'));
                                    });
                                });
                        });
                },
                'tractors' => function ($query) use ($startTime, $endTime, $dateStart, $dateEnd) {
                    $query->where('status', 'active')
                        ->whereDoesntHave('unavailability', function ($q) use ($dateStart, $dateEnd) {
                            $q->where('start_at', '<=', $dateEnd)
                                ->where('end_at', '>=', $dateStart);
                        })
                        ->whereDoesntHave('bookings', function ($q) use ($startTime, $endTime) {
                            $q->whereDate('slot_date', $startTime->toDateString())
                                ->where('payment_status', 'confirmed')
                                ->where(function ($timeQ) use ($startTime, $endTime) {
                                    $timeQ->where(function ($overlap) use ($startTime, $endTime) {
                                        $overlap->whereTime('start_time', '<', $endTime->format('H:i'))
                                            ->whereTime('end_time', '>', $startTime->format('H:i'));
                                    });
                                });
                        });
                },
                'units' => function ($query) use ($equipmentTypeId, $startTime, $endTime, $dateStart, $dateEnd) {
                    $query->where('equipment_type_id', $equipmentTypeId)
                        ->where('status', 'active')
                        ->whereDoesntHave('unavailability', function ($q) use ($dateStart, $dateEnd) {
                            $q->where('start_at', '<=', $dateEnd)
                                ->where('end_at', '>=', $dateStart);
                        })
                        ->whereDoesntHave('bookings', function ($q) use ($startTime, $endTime) {
                            $q->whereDate('slot_date', $startTime->toDateString())
                                ->where('payment_status', 'confirmed')
                                ->where(function ($timeQ) use ($startTime, $endTime) {
                                    $timeQ->where(function ($overlap) use ($startTime, $endTime) {
                                        $overlap->whereTime('start_time', '<', $endTime->format('H:i'))
                                            ->whereTime('end_time', '>', $startTime->format('H:i'));
                                    });
                                });
                        });
                }
            ])->get();

        // Find the first partner with available resources
        foreach ($availablePartners as $partner) {
            $availableDriver = $partner->drivers->first();
            $availableEquipment = $partner->units->first();

            if (!$availableDriver || !$availableEquipment) {
                continue;
            }

            $availableTractor = null;
            if ($equipmentType->requires_tractor) {
                $availableTractor = $partner->tractors->first();
                if (!$availableTractor) {
                    continue; // Skip if tractor required but not available
                }
            }

            // Return the available resources
            return [
                'partner_id' => $partner->id,
                'driver_id' => $availableDriver->id,
                'tractor_id' => $availableTractor ? $availableTractor->id : null,
                'equipment_unit_id' => $availableEquipment->id
            ];
        }

        return false; // No available resources found
    }

    /**
     * Check if slot conflicts with existing bookings (with buffer time)
     */
    private function hasSlotConflict($areaId, $equipmentTypeId, $slotDate, $startTime, $endTime, $bufferMinutes = 30)
    {
        $bufferedStart = Carbon::parse($slotDate . ' ' . $startTime)->subMinutes($bufferMinutes);
        $bufferedEnd = Carbon::parse($slotDate . ' ' . $endTime)->addMinutes($bufferMinutes);

        return Booking::where('slot_date', $slotDate)
            ->where('area_id', $areaId)
            ->where('equipment_type_id', $equipmentTypeId)
            ->where(function ($q) {
                $q->where('payment_status', 'confirmed')
                    ->orWhere(function ($q2) {
                        $q2->where('payment_status', 'pending')
                            ->where('reserved_until', '>', now());
                    });
            })
            ->where(function ($timeQuery) use ($bufferedStart, $bufferedEnd) {
                $timeQuery->where(function ($overlap) use ($bufferedStart, $bufferedEnd) {
                    $overlap->whereTime('start_time', '<', $bufferedEnd->format('H:i'))
                        ->whereTime('end_time', '>', $bufferedStart->format('H:i'));
                });
            })
            ->exists();
    }

    public function cancelBooking(Request $request)
    {
        // dd($request->all());
        try {
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'cancel_reason' => 'nullable|string|max:255',
            ]);

            $booking = Booking::find($request->booking_id);

            if ($booking->status == 'cancelled') {
                return $this->responseWithError('Booking already cancelled', 422);
            }

            $now = now();
            $startTime = Carbon::parse($booking->slot_date . ' ' . $booking->start_time);
            // Check if service is already started or partially used
            if ($now->greaterThanOrEqualTo($startTime)) {
                return $this->responseWithError('Cannot cancel after service started or partially used.', 403);
            }

            $refundType = 'none';
            $refundAmount = 0;

            // Check for weather/natural reason
            if (strtolower($request->cancel_reason) === 'weather') {
                $refundType = 'full';
                $refundAmount = $booking->price; // assuming this field exists
            } else {
                $diffInMinutes = $startTime->diffInMinutes($now);

                if ($diffInMinutes >= 120) {
                    $refundType = 'full';
                    $refundAmount = $booking->price;
                } elseif ($diffInMinutes < 120) {
                    $refundType = 'partial';
                    $refundAmount = $booking->price * 0.80; // 20% charge
                }
            }
            // Update booking status
            $booking->status = "cancelled";
            $booking->cancelled_at = now();
            $booking->cancel_reason = $request->cancel_reason ?? null;
            $booking->refund_amount = $refundAmount;
            $booking->refund_status = 'pending'; // Or 'pending'
            $booking->save();

            // Optionally: dispatch refund job or API call here

            return $this->responseWithSuccess($booking, "Booking cancelled successfully. Refund Type: {$refundType}", 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    public function assignBookings(Request $request)
    {
        try {
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'partner_id' => 'required|exists:partners,id',
                'driver_id' => 'required|exists:drivers,id',
                'tractor_id' => 'required|exists:tractors,id',
                'equipment_unit_id' => 'required|exists:equipment_units,id'
            ]);

            $booking = Booking::find($request->booking_id);
            $booking->partner_id = $request->partner_id;
            $booking->driver_id = $request->driver_id;
            $booking->tractor_id = $request->tractor_id;
            $booking->equipment_unit_id = $request->equipment_unit_id;
            $booking->admin_note = $request->admin_note;
            $booking->booking_status = "confirmed";
            $booking->save();

            return $this->responseWithSuccess($booking, "Booking assigned successfully", 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            return $this->responseWithError('Validation failed', 422, $errors);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
