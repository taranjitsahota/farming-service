<?php

namespace App\Http\Controllers;

use App\Models\BusinessTiming;
use Illuminate\Http\Request;

class BusinessTimingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $timing = BusinessTiming::all();
            return $this->responseWithSuccess($timing, ' Business timing fetched successfully', 200);
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
                'day' => 'required|unique:business_timings',
                'start_time' => 'required',
                'end_time' => 'required'
            ]);

            $timing = BusinessTiming::create($request->all());
            return $this->responseWithSuccess($timing, 'timing created successfully', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseWithError($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $timing = BusinessTiming::find($id);
            return $this->responseWithSuccess($timing, 'timing fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'day' => 'required',
                'start_time' => 'required',
                'end_time' => 'required'
            ]);

            $timing = BusinessTiming::find($id);
            $timing->update($request->all());
            return $this->responseWithSuccess($timing, 'timing updated successfully', 200);
        }
        catch (\Illuminate\Validation\ValidationException $e){
            return $this->responseWithError($e->getMessage(), 422); 
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $timing = BusinessTiming::find($id);
            $timing->delete();
            return $this->responseWithSuccess($timing, 'timing deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
