<?php

namespace App\Http\Controllers;

use App\Models\PartnerUnavailability;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PartnerUnavailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = PartnerUnavailability::with('users')->get();
            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'partner_id' => $item->partner_id,
                    'partner_name' => $item->partner->user->name,
                    'partner_id' => $item->partner_id,
                    'start_at' => $item->start_at ? $item->start_at->format('Y-m-d') : null,
                    'end_at' => $item->end_at ? $item->start_at->format('Y-m-d') : null,
                    'reason' => $item->reason,
                ];
            });
            return $this->responseWithSuccess($formattedData, 'Partner Unavailability fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Unavailability not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required',
            'start_at' => 'required',
            'end_at' => 'required',
            'reason' => 'required',
        ]);
        try {
            $data = PartnerUnavailability::create($request->all());
            return $this->responseWithSuccess($data, 'Partner Unavailability created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Unavailability not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = PartnerUnavailability::find($id);
            return $this->responseWithSuccess($data, 'Partner Unavailability fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Unavailability not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'partner_id' => 'required',
            'start_at' => 'required',
            'end_at' => 'required',
            'reason' => 'required',
        ]);

        try {
            $data = PartnerUnavailability::find($id);
            $data->update($request->all());
            return $this->responseWithSuccess($data, 'Partner Unavailability updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Unavailability not found');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = PartnerUnavailability::find($id);
            $data->delete();
            return $this->responseWithSuccess($data, 'Partner Unavailability deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Unavailability not found');
        }
    }
}
