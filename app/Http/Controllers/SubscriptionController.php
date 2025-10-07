<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $subscriptions = Subscription::with('user')->where('status', 'active')->get();
            $formatter = $subscriptions->map(function ($subscriptions) {
                return [
                    'name' => $subscriptions->user->name,
                    'user_id' => $subscriptions->user_id,
                    'email' => $subscriptions->user->email,
                    'phone' => $subscriptions->user->phone,
                    'id' => $subscriptions->id,
                    'kanals' => $subscriptions->kanals,
                    'total_price' => $subscriptions->total_price,
                    'status' => $subscriptions->status,
                    'price_per_kanal' => $subscriptions->price_per_kanal,
                    'location' => $subscriptions->location,
                    'start_date' => $subscriptions->start_date,
                    'end_date' => $subscriptions->end_date,
                ];
            });
            return $this->responseWithSuccess($formatter, 'Subscriptions fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:subscription_plans,id',
                'kanals' => 'required|integer|min:4'
            ]);

            $user = auth()->user();
            $kanals = $request->kanals;
            $plan = SubscriptionPlan::findOrFail($request->plan_id);

            if (!$plan->razorpay_plan_id) {
                return $this->responseWithError('Plan missing Razorpay plan ID', 400);
            }

            $planId = $plan->razorpay_plan_id;

            $razorpay = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

            $subscription = $razorpay->subscription->create([
                'plan_id' => $planId,
                'total_count' => 11, // months
                'quantity' => $kanals,
                'customer_notify' => 1,
                'start_at' => now()->addMinutes(5)->timestamp,
                'notes' => [
                    'user_id' => $user->id,
                    'kanals' => $kanals,
                ]
            ]);

            $amount = $kanals * $plan->price_per_kanal;

            // Save draft subscription (optional)
            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'razorpay_subscription_id' => $subscription->id,
                'land_area' => $kanals,
                'total_price' => $amount,
                'price_per_kanal' => $plan->price_per_kanal,
                'kanals' => $kanals,
                'start_date' => \Carbon\Carbon::createFromTimestamp($subscription->current_start),
                'end_date' => \Carbon\Carbon::createFromTimestamp($subscription->current_end),
                'status' => 'created',
            ]);

            $data = [
                'subscription_id' => $subscription->id,
                'razorpay_key' => config('services.razorpay.key'),
                'currency' => 'INR'

            ];

            return $this->responseWithSuccess($data, 'Subscription created successfully', 200);
        } catch (ValidationException $e) {
            return $this->responseWithError($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $subscription = Subscription::with(['user', 'plan'])->findOrFail($id);
            return $this->responseWithSuccess($subscription, 'Subscription fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:subscription_plans,id',
                'land_area' => 'required|numeric|min:4',
            ]);

            $subscription = Subscription::findOrFail($id);
            $subscription->update($request->only(['plan_id', 'land_area']));

            return $this->responseWithSuccess($subscription, 'Subscription updated successfully', 200);
        } catch (ValidationException $e) {
            return $this->responseWithError($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $subscriptions = Subscription::find($id);
            $subscriptions->delete();
            return $this->responseWithSuccess($subscriptions, 'Subscriptions deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
    // public function verifySubscription($razorpaySubscriptionId)
    // {
    //     $razorpay = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

    //     $razorpaySubscriptionId = trim((string)$razorpaySubscriptionId);
    //     // dd($razorpaySubscriptionId);
    //     // Fetch from Razorpay API
    //     $subscription = $razorpay->subscription->fetch($razorpaySubscriptionId);
    //     // dd($subscription);
    //     if ($subscription->status === 'authenticated') {
    //         Subscription::where('razorpay_subscription_id', 'sub_R3Zv4ABq2cx27E')
    //             ->update(['status' => 'active']);
    //         return $this->responseWithSuccess($subscription, 'Subscription verified successfully', 200);
    //     }
    //     return $this->responseWithSuccess($subscription, 'Subscription not verified', 200);
    // }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_subscription_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $paymentId = $request->razorpay_payment_id;
        $subscriptionId = $request->razorpay_subscription_id;
        $signature = $request->razorpay_signature;
        $secret = config('services.razorpay.secret');

        $expected = hash_hmac('sha256', $paymentId . '|' . $subscriptionId, $secret);

        if (!hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $api = new Api(config('services.razorpay.key'), $secret);
        $payment = $api->payment->fetch($paymentId);

        $sub = Subscription::where('razorpay_subscription_id', $subscriptionId)->first();
        if (!$sub) {
            return response()->json(['message' => 'Subscription not found'], 404);
        }


        // if (SubscriptionPayment::where('payment_id', $paymentId)->exists()) {
        //     return response()->json(['message' => 'Already verified'], 200);
        // }

        // record first payment
        SubscriptionPayment::create([
            'subscription_id' => $sub->id,
            'razorpay_payment_id' => $paymentId,
            'amount' => $payment->amount / 100,
            'currency' => $payment->currency,
            'status' => $payment->status, // captured/authorized
            'paid_at' => now(),
            'payload' => $payment->toArray(),
        ]);

        // mark subscription active locally
        $sub->update([
            'status' => 'active',
            'start_date' => now(),
            'next_billing_date' => now()->addMonth(),
            // 'cycles_remaining' => $sub->total_count - 1, // if you track cycles
        ]);

        // grant access to user features immediately
        $user = $sub->user;
        $user->update(['is_subscribed' => true]);

        // respond to frontend
        return response()->json(['message' => 'Subscription activated'], 200);
    }
}
