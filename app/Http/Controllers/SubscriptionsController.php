<?php

namespace App\Http\Controllers;

use App\Models\Subscriptions;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $subscriptions = Subscriptions::where('user_id', auth()->id())->get();
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
                'land_area' => 'required|numeric|min:4'
            ]);

            $rate = $request->plan_type === 'sugarcane' ? 1500 : 2500;
            $discountedRate = $rate * 0.9;
            $total = $discountedRate * $request->land_area;

            // Create 25% upfront order
            $initialPayment = $total * 0.25;

            $razorpay = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

            $razorpayOrder = $razorpay->order->create([
                'receipt' => Str::uuid(),
                'amount' => $initialPayment * 100,
                'currency' => 'INR',
                'payment_capture' => 1
            ]);

            // Save draft subscription (optional)
            Subscriptions::create([
                'user_id' => auth()->id(),
                'plan_type' => $request->plan_type,
                'land_area' => $request->land_area,
                'total_price' => $total,
                'razorpay_order_id' => $razorpayOrder->id,
                'start_date' => today(),
                'end_date' => today()->addYear(),
            ]);

            $data = [
                'order_id' => $razorpayOrder->id,
                'amount' => $initialPayment,
                'currency' => 'INR'

            ];

            return $this->responseWithSuccess($data, 'Order created successfully', 200);
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
            $subscriptions = Subscriptions::find($id);
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

            $subscriptions = Subscriptions::find($id);
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
            $subscriptions = Subscriptions::find($id);
            $subscriptions->delete();
            return $this->responseWithSuccess($subscriptions, 'Subscriptions deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
