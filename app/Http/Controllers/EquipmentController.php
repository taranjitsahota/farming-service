<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $equipment = Equipment::with('substation')->get();
            $formatter = $equipment->map(function ($equipment) {
                return [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'substation_name' => $equipment->substation->name,
                    'substation_id' => $equipment->substation->id,
                    'is_enabled' => $equipment->is_enabled,
                    'image' => $equipment->image,
                    'price_per_kanal' => $equipment->price_per_kanal,
                    'min_kanal' => $equipment->min_kanal,
                    'minutes_per_kanal' => $equipment->minutes_per_kanal,
                    'inventory' => $equipment->inventory


                ];
            });

            return $this->responseWithSuccess($formatter, 'equipment fetched successfully', 200);
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
            'substation_id' => 'required|exists:substations,id',
            'price_per_kanal' => 'required|numeric',
            'min_kanal' => 'required|integer',
            'is_enabled' => 'required|boolean',
            'minutes_per_kanal' => 'required|integer',
            'inventory' => 'required|integer',
            'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = $request->file('image')->store("equipments/{$request->name}", 's3');

        /** @var \Illuminate\Contracts\Filesystem\Cloud $disk */
        $disk = Storage::disk('s3');
            $url = $disk->url($path);

            // $request->merge([
            //     'image' => $url,
            //     'image_path' => $path,
            // ]);

            $equipment = Equipment::create([
                'name' => $request->name,
                'substation_id' => $request->substation_id,
                'price_per_kanal' => $request->price_per_kanal,
                'min_kanal' => $request->min_kanal,
                'is_enabled' => $request->is_enabled,
                'minutes_per_kanal' => $request->minutes_per_kanal,
                'inventory' => $request->inventory,
                'image' => $url,
            ]);

            return $this->responseWithSuccess($equipment, 'equipment created successfully', 200);


        // $equipment = Equipment::create($request->all());
        // return $this->responseWithSuccess($equipment, 'equipment created successfully', 200);
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
            // 'substation_id' => 'required|exists:substations,id',
            'is_enabled' => 'required',
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
