<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Subscriptions;
use App\Models\User;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $secret = config('services.razorpay.webhook_secret');

        // ✅ Verify webhook signature
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Razorpay webhook signature mismatch', [
                'expected' => $expectedSignature,
                'received' => $signature,
            ]);
            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        try {
            switch ($event['event']) {
                case 'subscription.charged':
                    $this->handleSubscriptionCharged($event);
                    break;

                case 'payment.failed':
                    $this->handlePaymentFailed($event);
                    break;

                case 'subscription.cancelled':
                case 'subscription.paused':
                case 'subscription.completed':
                    $this->handleSubscriptionStatusUpdate($event);
                    break;

                default:
                    Log::info('Unhandled Razorpay event: ' . $event['event']);
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage(), [
                'event' => $event ?? null
            ]);
            return response('Error', 500);
        }
    }

    private function handleSubscriptionCharged(array $event): void
    {
        $payment = $event['payload']['payment']['entity'];
        $subscription = $event['payload']['subscription']['entity'];

        $sub = Subscription::where('razorpay_subscription_id', $subscription['id'])->first();
        if (!$sub) {
            Log::warning('Subscription not found for charged event', ['id' => $subscription['id']]);
            return;
        }

        // Idempotency: don’t create duplicate payment rows
        if (SubscriptionPayment::where('razorpay_payment_id', $payment['id'])->exists()) {
            return;
        }

        SubscriptionPayment::create([
            'subscription_id' => $sub->id,
            'razorpay_payment_id' => $payment['id'],
            'amount' => $payment['amount'] / 100,
            'currency' => $payment['currency'],
            'status' => $payment['status'],
            'paid_at' => now(),
            'payload' => $payment,
        ]);

        // Update subscription status & next billing
        $sub->update([
            'status' => 'active',
            'next_billing_date' => now()->addMonth(), // adjust if needed
        ]);

        Log::info('Subscription charged successfully', [
            'subscription_id' => $sub->id,
            'payment_id' => $payment['id']
        ]);
    }

    private function handlePaymentFailed(array $event): void
    {
        $payment = $event['payload']['payment']['entity'];
        $subscriptionId = $payment['subscription_id'] ?? null;

        if (!$subscriptionId) {
            Log::warning('Payment failed without subscription_id', $payment);
            return;
        }

        $sub = Subscription::where('razorpay_subscription_id', $subscriptionId)->first();
        if (!$sub) {
            Log::warning('Subscription not found for payment.failed', ['id' => $subscriptionId]);
            return;
        }

        SubscriptionPayment::create([
            'subscription_id' => $sub->id,
            'razorpay_payment_id' => $payment['id'],
            'amount' => $payment['amount'] / 100,
            'currency' => $payment['currency'],
            'status' => 'failed',
            'paid_at' => now(),
            'payload' => $payment,
        ]);

        // Optional: pause access until next attempt
        $sub->update(['status' => 'past_due']);

        Log::info('Payment failed for subscription', [
            'subscription_id' => $sub->id,
            'payment_id' => $payment['id']
        ]);
    }

    private function handleSubscriptionStatusUpdate(array $event): void
    {
        $subscription = $event['payload']['subscription']['entity'];
        $sub = Subscription::where('razorpay_subscription_id', $subscription['id'])->first();

        if (!$sub) {
            Log::warning('Subscription not found for status update', ['id' => $subscription['id']]);
            return;
        }

        $status = $event['event'] === 'subscription.cancelled' ? 'cancelled'
            : ($event['event'] === 'subscription.paused' ? 'paused'
                : 'completed');

        $sub->update(['status' => $status]);

        Log::info('Subscription status updated', [
            'subscription_id' => $sub->id,
            'new_status' => $status
        ]);
    }
}
