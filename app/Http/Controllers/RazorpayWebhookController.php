<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Subscription;
use App\Models\Subscriptions;
use App\Models\User;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $secret = config('services.razorpay.webhook_secret'); // from .env

        // Verify signature
        if (!$this->verifySignature($payload, $signature, $secret)) {
            return response('Invalid signature', 400);
        }

        $event = $request->input('event');

        match ($event) {
            'payment.captured' => $this->handleInitialPayment($request->input('payload.payment.entity')),
            'subscription.charged' => $this->handleInstallmentCharge($request->input('payload.payment.entity')),
            'payment.failed' => $this->handleFailedPayment($request->input('payload.payment.entity')),
            default => Log::info("Unhandled event: $event"),
        };

        return response('OK', 200);
    }

    protected function verifySignature($payload, $signature, $secret)
    {
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    protected function handleInitialPayment($payment)
    {
        $orderId = $payment['order_id'];

        // Find subscription draft based on order_id (e.g., stored temporarily)
        $subscription = Subscriptions::where('razorpay_order_id', $orderId)->first();

        if ($subscription) {
            $subscription->update([
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addYear(),
            ]);
            Log::info("Subscription activated for user {$subscription->user_id}");
        }
    }

    protected function handleInstallmentCharge($payment)
    {
        // Optional logic to update installment status
        // Log::info("Installment received: ₹{$payment['amount'] / 100}");
    }

    protected function handleFailedPayment($payment)
    {
        // Optional: Notify user/admin
        Log::warning("Payment failed: {$payment['error_description']}");
    }
}