<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BusinessTiming;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{


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
            $service = Service::findOrFail($request->service_id);
            $area = $request->area;

            if ($service->min_area && $area < $service->min_area) {
                return $this->responseWithError('Minimum area is ' . $service->min_area . ' kanals', 422);
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
            $total_minutes = $area * $service->minutes_per_kanal;
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

                $conflict = false;
                foreach ($bookings as $booking) {
                    if (
                        $slot_end->gt($booking->start_time) &&
                        $current_start->lt($booking->end_time)
                    ) {
                        $current_start = $booking->end_time->copy(); // move to next slot after booking
                        $conflict = true;
                        break;
                    }
                }

                if (!$conflict) {
                    $slots[] = [
                        'start_time' => $current_start->format('H:i'),
                        'end_time' => $slot_end->format('H:i'),
                        'duration_required_minutes' => $total_minutes,
                    ];
                    $current_start = $slot_end->copy(); // move to next slot
                }
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

        $service = Service::findOrFail($request->service_id);
        $area = $request->area;
        // Check minimum area allowed
        if ($service->min_area && $area < $service->min_area) {
            return $this->responseWithError('Minimum area is ' . $service->min_area . ' kanals', 422);
        }

        $rate_per_kanal = $service->price;
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
    //         $request->validate([
    //             'date' => 'required|date',
    //             'crop_id' => 'required|exists:crops,id',
    //             'area_id' => 'required|exists:areas,id',
    //             'service_id' => 'required|exists:services,id',
    //             'area' => 'required|numeric|min:1',
    //             'start_time' => 'required',
    //         ]);

    //         $service = Service::findOrFail($request->service_id);

    //         if ($service->min_area && $request->area < $service->min_area) {
    //             return $this->responseWithError('Minimum area is ' . $service->min_area . ' kanals', 422);
    //         }

    //         $slotDateTime = Carbon::parse($request->date . ' ' . $request->start_time);
    //         if (now()->diffInMinutes($slotDateTime, false) < 1440) {
    //             return $this->responseWithError('Bookings must be made at least 24 hours in advance.', 422);
    //         }

    //         $buffer_minutes = 30;

    //         $start_time = Carbon::parse($request->start_time);
    //         $end_time = $start_time->copy()->addMinutes($request->area * $service->minutes_per_kanal);

    //         // Extend user's slot with buffer on both sides
    //         $buffered_user_start = $start_time->copy()->subMinutes($buffer_minutes);
    //         $buffered_user_end = $end_time->copy()->addMinutes($buffer_minutes);

    //         // Check for conflicts with existing bookings + buffer
    //         $conflict = Booking::where('slot_date', $request->date)
    //             ->where(function ($q) use ($buffered_user_start, $buffered_user_end) {
    //                 $q->whereBetween('start_time', [$buffered_user_start, $buffered_user_end])
    //                     ->orWhereBetween('end_time', [$buffered_user_start, $buffered_user_end])
    //                     ->orWhere(function ($q2) use ($buffered_user_start, $buffered_user_end) {
    //                         $q2->where('start_time', '<=', $buffered_user_start)
    //                             ->where('end_time', '>=', $buffered_user_end);
    //                     });
    //             })
    //             ->exists();

    //         if ($conflict) {
    //             return $this->responseWithError('Slot is already booked or too close to another booking (30-min buffer required)', 422);
    //         }

    //         $price = $service->price * $request->area;

    //         Booking::create([
    //             'user_id' => auth()->id(),
    //             'service_id' => $service->id,
    //             'crop_id' => $request->crop_id,
    //             'area_id' => $request->area_id,
    //             'slot_date' => $request->date,
    //             'start_time' => $start_time->format('H:i'),
    //             'end_time' => $end_time->format('H:i'),
    //             'area' => $request->area,
    //             'price' => $price,
    //         ]);

    //         return $this->responseWithSuccess([], 'Slot Booked Successfully', 200);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return $this->responseWithError('Validation failed', 422, $e->errors());
    //     } catch (\Exception $e) {
    //         return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
    //     }
    // }
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


            $service = Service::findOrFail($request->service_id);

            if ($service->min_area && $request->area < $service->min_area) {
                return $this->responseWithError('Minimum area is ' . $service->min_area . ' kanals', 422);
            }

            $alreadyPending = Booking::where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('reserved_until', '>', now())
                ->exists();

            if ($alreadyPending) {
                return $this->responseWithError('You already have a pending booking. Please complete payment.', 422);
            }

            $price = $service->price * $request->area;
            // dd($price);
            $area = $request->area;
            $duration = $area * $service->minutes_per_kanal;
            $start = Carbon::parse($request->slot_date . ' ' . $request->start_time);
            $end = $start->copy()->addMinutes($duration);

            // Check conflict with confirmed & pending bookings
            $conflict = Booking::where('slot_date', $request->slot_date)
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_time', [$start, $end])
                        ->orWhereBetween('end_time', [$start, $end]);
                })
                ->where(function ($q) {
                    $q->where('status', 'confirmed')
                        ->orWhere(function ($q2) {
                            $q2->where('status', 'pending')
                                ->where('reserved_until', '>', now());
                        });
                })
                ->exists();


            if ($conflict) {
                return $this->responseWithError('Selected slot is already booked or reserved', 422);
            }

            // Reserve slot with status 'pending'
            $booking = Booking::create([
                'user_id' => $user->id,
                'service_id' => $request->service_id,
                'crop_id' => $request->crop_id,
                'area_id' => $request->area_id,
                'price' => $price,
                'slot_date' => $request->slot_date,
                'start_time' => $start,
                'end_time' => $end,
                'service_area_id' => $area,
                'status' => 'pending',
                'reserved_until' => now()->addMinutes(15)
            ]);

            return $this->responseWithSuccess(['booking_id' => $booking->id], 'Slot reserved. Proceed to payment.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
