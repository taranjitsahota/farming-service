<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BusinessTiming;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Traits\Subscriptions\isSubscribed;
use Illuminate\Support\Facades\DB;

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
                    'payment_status' => $booking->payment_status,
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
        try {
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
            DB::beginTransaction();
            $request->validate([
                'service_id' => 'required|exists:services,id',
                'slot_date' => 'required|date',
                'start_time' => 'required',
                'area' => 'required|numeric|min:1',
                'crop_id' => 'required|exists:crops,id',
                'service_area_id' => 'required|exists:serviceareas,id',
                'substation_id' => 'required|exists:substations,id'
            ]);

            $user = auth()->user();


            $service = Service::with('equipment')->findOrFail($request->service_id);
            $equipment = $service->equipment;

            if ($equipment->min_kanal && $request->area < $equipment->min_kanal) {
                return $this->responseWithError('Minimum area is ' . $equipment->min_kanal . ' kanals', 422);
            }

            // $alreadyPending = Booking::where('user_id', $user->id)
            //     ->where('service_area_id', $request->service_area_id)
            //     ->where('substation_id', $request->substation_id)
            //     ->where('payment_status', 'pending')
            //     ->where('reserved_until', '>', now())
            //     ->exists();

            // if ($alreadyPending) {
            //     return $this->responseWithError('You already have a pending booking. Please complete payment.', 422);
            // }

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
                'substation_id' => $request->substation_id,
                'crop_id' => $request->crop_id,
                'area_id' => $request->area_id,
                'land_area' =>  $area,
                'price' => $subscribed ? 0 : $price, // No price if subscribed
                'slot_date' => $request->slot_date,
                'start_time' => $start,
                'end_time' => $end,
                'duration' => $duration,
                'service_area_id' => $request->service_area_id,
                'payment_status' => $status,
                'reserved_until' => $reservedUntil
            ]);

            $message = $subscribed
                ? 'Slot booked using your subscription. No payment required.'
                : 'Slot reserved. Proceed to payment.';

            DB::commit();
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
