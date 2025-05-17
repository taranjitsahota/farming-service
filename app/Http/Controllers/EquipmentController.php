<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $equipment = Equipment::all();
            return $this->responseWithSuccess($equipment, 'equipment fetched successfully', 200);
        }catch(\Exception $e){
            return $this->responseWithError($e->getMessage(), 'equipment not found', 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price_per_kanal' => 'required|numeric',
            'min_kanal' => 'required|integer',
            // 'is_available' => 'required|boolean',
            'minutes_per_kanal' => 'required|integer',
            'inventory' => 'required|integer',
            'image' => 'nullable|url'
        ]);

        $equipment = Equipment::create($request->all());
        return $this->responseWithSuccess($equipment, 'equipment created successfully', 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $equipment = Equipment::findOrFail($id);
            return $this->responseWithSuccess($equipment, 'equipment fetched successfully', 200);
        }catch(\Exception $e){
            return $this->responseWithError($e->getMessage(), 'equipment not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            // 'name' => 'required|string|max:255',
            // 'price_per_kanal' => 'required|numeric',
            // 'min_kanal' => 'required|integer',
            'is_enabled' => 'required|boolean',
            // 'minutes_per_kanal' => 'required|integer',
            // 'inventory' => 'required|integer',
            // 'image' => 'nullable|url'
        ]);

        $equipment = Equipment::findOrFail($id);
        $equipment->update($request->all());
        return $this->responseWithSuccess($equipment, 'equipment updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $equipment = Equipment::findOrFail($id);
            $equipment->delete();
            return $this->responseWithSuccess(null, 'equipment deleted successfully', 200);
        }catch(\Exception $e){
            return $this->responseWithError($e->getMessage(), 'equipment not found', 404);
        }
    }
}
