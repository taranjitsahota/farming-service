<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use Razorpay\Api\Api;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\BookingConfirmationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class RazorPayController extends Controller
{
    public function createRazorpayOrder(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'area' => 'required|numeric|min:1'
        ]);

        $service = Service::findOrFail($request->service_id);
        $area = $request->area;

        if ($service->min_area && $area < $service->min_area) {
            return $this->responseWithError('Minimum area is ' . $service->min_area . ' kanals', 422);
        }

        $amount = $area * $service->price_per_kanal;

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $razorpayOrder = $api->order->create([
            'receipt'         => Str::uuid(),
            'amount'          => $amount * 100, // Amount in paise
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
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required'
        ]);

        try {
            $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            $payment = $api->payment->fetch($request->razorpay_payment_id);

            if ($payment->status == 'captured') {
                // Save to DB: create Booking entry
                return $this->responseWithSuccess(null, 'Payment verified & booking confirmed');
            }

            return $this->responseWithError('Payment not captured', 400);
        } catch (\Exception $e) {
            return $this->responseWithError('Payment verification failed', 500);
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
