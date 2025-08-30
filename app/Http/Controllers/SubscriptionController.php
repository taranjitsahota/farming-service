<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
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
            $subscriptions = Subscription::with('user')->get();
            $formatter = $subscriptions->map(function ($subscriptions) {
                return [
                    'name' => $subscriptions->user->name,
                    'user_id' => $subscriptions->user_id,
                    'email' => $subscriptions->user->email,
                    'phone' => $subscriptions->user->phone,
                    'id' => $subscriptions->id,
                    'plan_type' => $subscriptions->plan_type,
                    'kanals' => $subscriptions->kanals,
                    'total_price' => $subscriptions->total_price,
                    'status' => $subscriptions->status,
                    'price_per_kanal' => $subscriptions->price_per_kanal,
                    'location' => $subscriptions->location,
                    'start_date' => $subscriptions->start_date,
                    'end_date' => $subscriptions->end_date,
                ];
            });
            return $this->responseWithSuccess($subscriptions, 'Subscriptions fetched successfully', 200);
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
                'plan_type' => 'required|in:general,sugarcane',
                'kanals' => 'required|integer|min:4'
            ]);

            // $rate = $request->plan_type === 'sugarcane' ? 1500 : 2500;
            // $discountedRate = $rate * 0.9;
            // $total = $discountedRate * $request->land_area;

            // Create 25% upfront order
            // $initialPayment = $total * 0.25;

            $user = auth()->user();
            $kanals = $request->kanals;
            $planType = $request->plan_type;

            $planId = match ($planType) {
                'general' => 'plan_RBdfbscFchaqwB',
                'sugarcane' => 'plan_R37Gn4A4jTYoh8',
            };

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
            // $amount = $subscription->amount;
            $amount = $kanals * ($planType === 'general' ? 2500 : 1500);

            // Save draft subscription (optional)
            Subscription::create([
                'user_id' => $user->id,
                'razorpay_subscription_id' => $subscription->id,
                'plan_type' => $planType,
                'land_area' => $kanals,
                'total_price' => $amount,
                'price_per_kanal' => $planType === 'general' ? 2500 : 1500,
                'start_date' => now()->addMinutes(5),
                'end_date' => now()->addMonths(11),
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
            $subscriptions = Subscription::find($id);
            return $this->responseWithSuccess($subscriptions, 'Subscriptions fetched successfully', 200);
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
                'plan_type' => 'required|in:general,sugarcane',
                'land_area' => 'required|numeric|min:4'
            ]);

            $subscriptions = Subscription::find($id);
            $subscriptions->update($request->all());
            return $this->responseWithSuccess($subscriptions, 'Subscriptions updated successfully', 200);
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
    public function verifySubscription($razorpaySubscriptionId)
    {
        $razorpay = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $razorpaySubscriptionId = trim((string)$razorpaySubscriptionId);
        // dd($razorpaySubscriptionId);
        // Fetch from Razorpay API
        $subscription = $razorpay->subscription->fetch($razorpaySubscriptionId);
        // dd($subscription);
        if ($subscription->status === 'authenticated') {
            Subscription::where('razorpay_subscription_id', 'sub_R3Zv4ABq2cx27E')
                ->update(['status' => 'active']);
            return $this->responseWithSuccess($subscription, 'Subscription verified successfully', 200);
        }
        return $this->responseWithSuccess($subscription, 'Subscription not verified', 200);
    }
}
