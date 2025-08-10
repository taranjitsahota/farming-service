<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use Razorpay\Api\Api;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\BookingConfirmationMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RazorPayController extends Controller
{
    public function createRazorpayOrder(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'area' => 'required|numeric|min:1'
        ]);

        $service = Service::with('equipment')->findOrFail($request->service_id);
        $equipment = $service->equipment;
        $area = $request->area;

        if ($equipment->min_kanal && $area < $equipment->min_kanal) {
            return $this->responseWithError('Minimum area in kanals is ' . $equipment->min_kanal . ' kanals', 422);
        }

        $amount = $area * $equipment->price_per_kanal;

        if (!$amount) {
            return $this->responseWithError('Invalid amount', 422);
        }

        $amountinpaise = $amount * 100;



        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $razorpayOrder = $api->order->create([
            'receipt'         => Str::uuid(),
            'amount'          => $amountinpaise, // Amount in paise
            'currency'        => 'INR',
            'payment_capture' => 1 // Auto capture
        ]);

        return $this->responseWithSuccess([
            'order_id' => $razorpayOrder->id,
            'amount' => $razorpayOrder->amount,
            'currency' => $razorpayOrder->currency,
            'key' => config('services.razorpay.key'),
        ], 'Razorpay order created successfully');
    }

    public function verifyPayment(Request $request)
    {
        DB::beginTransaction();

        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'booking_id' => 'required|exists:bookings,id'
        ]);

        try {
            $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            $payment = $api->payment->fetch($request->razorpay_payment_id);

            if ($payment->status == 'captured') {
                // Save to DB: create Booking entry

                $booking = Booking::where('id', $request->booking_id)
                    ->where('user_id', auth()->id())
                    ->where('payment_status', 'pending')
                    ->first();

                if (!$booking) {
                    return $this->responseWithError('Invalid or expired booking', 400);
                }

                $start = $booking->start_time;
                $end = $booking->end_time;

                // $conflict = Booking::where('slot_date', $booking->slot_date)
                //     ->where(function ($q) use ($start, $end) {
                //         $q->whereBetween('start_time', [$start, $end])
                //             ->orWhereBetween('end_time', [$start, $end]);
                //     })
                //     ->where('status', 'confirmed')
                //     ->where('id', '!=', $booking->id)
                //     ->exists();

                // if ($conflict) {
                //     return $this->responseWithError('Slot just got booked. Please try another.', 409);
                // }

                $booking->update([
                    'payment_status' => 'confirmed',
                    'booking_status' => 'completed',
                    'payment_id' => $request->razorpay_payment_id,
                    'payment_method' => 'razorpay',
                    'paid_at' => now()
                ]);

                $user = $booking->user_id;
                $user = User::find($user);

                // Notification::send($booking->user, new BookingPaid($booking));
                // Mail::to($user->email)->send(new BookingConfirmationMail($booking));

                DB::commit();

                return $this->responseWithSuccess(null, 'Payment verified & booking confirmed',200);
            }

            return $this->responseWithError('Payment not captured', 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseWithError('Payment verification failed', 500, $e->getMessage());
        }
    }

    public function handleSuccess(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
            'booking_id' => 'required|exists:bookings,id'
        ]);

        // Optional: Verify signature (highly recommended)
        $generated_signature = hash_hmac(
            'sha256',
            $request->razorpay_order_id . "|" . $request->razorpay_payment_id,
            config('services.razorpay.secret')
        );

        if ($generated_signature !== $request->razorpay_signature) {
            return response()->json(['error' => 'Invalid Signature'], 400);
        }

        // Mark booking as paid
        $booking = Booking::find($request->booking_id);
        $booking->is_paid = 1;
        $booking->payment_id = $request->razorpay_payment_id;
        $booking->save();

        $user = $booking->user_id;
        $user = User::find($user);
        // Optionally notify user
        // Notification::send($booking->user, new BookingPaid($booking));
        Mail::to($user->email)->send(new BookingConfirmationMail($booking));

        return response()->json(['success' => true, 'message' => 'Payment successful']);
    }
}
