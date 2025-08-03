<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BusinessTiming;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Traits\Subscriptions\isSubscribed;

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
                ->with(['service', 'user', 'crop', 'servicearea'])
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
            $bookings = Booking::with('service')->with('user')->with('crop')->with('service')->with('service.equipment')->with('servicearea')->get();
            // dd($bookings);
            $formatted = $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'user_name' => $booking->user->name,
                    'phone' => $booking->user->phone,
                    'address' => $booking->address,
                    'equipment' => $booking->equipment,
                    'crop_id' => $booking->crop_id,
                    'crop_name' => $booking->crop->name,
                    'service_id' => $booking->service_id,
                    'equipment_name' => $booking->service->equipment->name,
                    'servicearea_id' => $booking->servicearea_id,
                    // 'servicearea_name' => $booking->servicearea->name,
                    'date' => $booking->slot_date,
                    'start_time' => $booking->start_time,
                    'end_time' => $booking->end_time,
                    'status' => $booking->status,
                   'booking_status' => $booking->booking_status,
                    'amount' => $booking->price,
                    'duration' => $booking->duration,
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
        try{
           $bookings = Booking::where('booking_status', 'pending')->with('service')->with('service.equipment')->with('user')->with('crop')->with('service')->with('servicearea')->get();

           $formatted = $bookings->map(function ($booking) {
               return [
                   'id' => $booking->id,
                   'user_id' => $booking->user_id,
                   'user_name' => $booking->user->name,
                    'phone' => $booking->user->phone,
                    'pin_code' => $booking->user->userInfo->pin_code ?? '',
                   'address' => $booking->address,
                   'land_area' => $booking->land_area,
                   'equipment' => $booking->equipment,
                   'user_note' => $booking->user_note,
                   'crop_id' => $booking->crop_id,
                   'crop_name' => $booking->crop->name,
                   'service_id' => $booking->service_id,
                   'equipment_name' => $booking->service->equipment->name,
                   'servicearea_id' => $booking->servicearea_id,
                   'service_name' => $booking->service->category,
                   'date' => $booking->slot_date,
                   'start_time' => $booking->start_time,
                   'end_time' => $booking->end_time,
                   'status' => $booking->status,
                   'booking_status' => $booking->booking_status,
                   'amount' => $booking->price,
                   'duration' => $booking->duration,
                   'area' => $booking->area,

                   'created_at' => $booking->created_at->format('Y-m-d'),


               ];   
           });
           
           return $this->responseWithSuccess($formatted, 'Bookings fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());

        }
    }

    public function getAvailableSlots(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'service_id' => 'required|exists:services,id',
                'area' => 'required|numeric|min:1'
            ]);

            $date = Carbon::parse($request->date)->startOfDay();
            $dayOfWeek = $date->format('l');
            $service = Service::with('equipment')->findOrFail($request->service_id);
            $equipment = $service->equipment;
            $area = $request->area;

            if ($equipment->min_kanal && $area < $equipment->min_kanal) {
                return $this->responseWithError('Minimum area in kanals is ' . $equipment->min_kanal . ' kanals', 422);
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
                ->where(function ($q) {
                    $q->where('status', 'confirmed')
                        ->orWhere(function ($q2) {
                            $q2->where('status', 'pending')
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

    public function bookSlot(Request $request)
    {
        try {
            $request->validate([
                'service_id' => 'required|exists:services,id',
                'slot_date' => 'required|date',
                'start_time' => 'required',
                'area' => 'required|numeric|min:1',
                'crop_id' => 'required|exists:crops,id',
                'service_area_id' => 'required|exists:serviceareas,id',
            ]);

            $user = auth()->user();


            $service = Service::with('equipment')->findOrFail($request->service_id);
            $equipment = $service->equipment;

            if ($equipment->min_kanal && $request->area < $equipment->min_kanal) {
                return $this->responseWithError('Minimum area is ' . $equipment->min_kanal . ' kanals', 422);
            }

            $alreadyPending = Booking::where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('reserved_until', '>', now())
                ->exists();

            if ($alreadyPending) {
                return $this->responseWithError('You already have a pending booking. Please complete payment.', 422);
            }

            $price = $equipment->price_per_kanal * $request->area;
            // dd($price);
            $area = $request->area;
            $duration = $area * $equipment->minutes_per_kanal;
            $start = Carbon::parse($request->slot_date . ' ' . $request->start_time);
            $end = $start->copy()->addMinutes($duration);

            // Check conflict with confirmed & pending bookings
            // $conflict = Booking::where('slot_date', $request->slot_date)
            //     ->where(function ($q) use ($start, $end) {
            //         $q->whereBetween('start_time', [$start, $end])
            //             ->orWhereBetween('end_time', [$start, $end]);
            //     })
            //     ->where(function ($q) {
            //         $q->where('status', 'confirmed')
            //             ->orWhere(function ($q2) {
            //                 $q2->where('status', 'pending')
            //                     ->where('reserved_until', '>', now());
            //             });
            //     })
            //     ->exists();


            // if ($conflict) {
            //     return $this->responseWithError('Selected slot is already booked or reserved', 422);
            // }

            // Reserve slot with status 'pending'
            // $booking = Booking::create([
            //     'user_id' => $user->id,
            //     'service_id' => $request->service_id,
            //     'crop_id' => $request->crop_id,
            //     'area_id' => $request->area_id,
            //     'price' => $price,
            //     'slot_date' => $request->slot_date,
            //     'start_time' => $start,
            //     'end_time' => $end,
            //     'duration' => $duration,
            //     'service_area_id' => $area,
            //     'status' => 'pending',
            //     'reserved_until' => now()->addMinutes(15)
            // ]);

            // return $this->responseWithSuccess(['booking_id' => $booking->id], 'Slot reserved. Proceed to payment.');

            $subscribed = $this->isSubscribed($user, $request->service_id, $request->area);

            $status = $subscribed ? 'confirmed' : 'pending';
            $reservedUntil = $subscribed ? null : now()->addMinutes(15);

            $booking = Booking::create([
                'user_id' => $user->id,
                'service_id' => $request->service_id,
                'crop_id' => $request->crop_id,
                'area_id' => $request->area_id,
                'land_area' =>  $area,
                'price' => $subscribed ? 0 : $price, // No price if subscribed
                'slot_date' => $request->slot_date,
                'start_time' => $start,
                'end_time' => $end,
                'duration' => $duration,
                'service_area_id' => $area,
                'status' => $status,
                'reserved_until' => $reservedUntil
            ]);

            $message = $subscribed
                ? 'Slot booked using your subscription. No payment required.'
                : 'Slot reserved. Proceed to payment.';

            return $this->responseWithSuccess(['booking_id' => $booking->id], $message);



        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
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
}
