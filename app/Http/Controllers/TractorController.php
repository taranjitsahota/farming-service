<?php

namespace App\Http\Controllers;

use App\Models\Tractor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TractorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $user = auth()->user();

            $query = Tractor::with('partner');

            if($user->hasRole('admin')) {
                $query->whereHas('partner.areas', function ($q) use ($user) {
                    $q->where('substation_id', $user->substation_id);
                });
            }

            $data = $query->get();
        
            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'partner_id' => $item->partner->id,
                    'partner_name' => $item->partner->user->name,
                    'registration_no' => $item->registration_no,
                    'status' => $item->status,
                ];
            });
            return $this->responseWithSuccess($formattedData, 'Tractor fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'partner_id' => 'required|exists:partners,id',
            'registration_no' => 'required|unique:tractors,registration_no',
            'status' => 'required',
        ]);
        try {
            $data = Tractor::create($request->all());
            return $this->responseWithSuccess($data, 'Tractor created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = Tractor::with('partner')->find($id);
            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'partner_id' => $item->partner->id,
                    'user_name' => $item->partner->name,
                    'registration_no' => $item->registration_no,
                    'status' => $item->status,
                ];
            });
            return $this->responseWithSuccess($formattedData, 'Tractor fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'partner_id' => 'required|exists:users,id',
            'registration_no' => 'required',
            'status' => 'required',
        ]);
        try {
            $data = Tractor::find($id);
            $data->update($request->all());
            return $this->responseWithSuccess($data, 'Tractor updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor not found');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = Tractor::find($id);
            $data->delete();
            return $this->responseWithSuccess($data, 'Tractor deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor not found');
        }
    }

    public function tractorByPartnerId($id){

        try {
            $data = Tractor::with('partner')->where('partner_id', $id)->get();
            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'partner_id' => $item->partner->id,
                    'partner_name' => $item->partner->user->name,
                    'registration_no' => $item->registration_no,
                    'status' => $item->status,
                ];
            });
            return $this->responseWithSuccess($formattedData, 'Tractor fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Tractor not found');
        }

    }
}
