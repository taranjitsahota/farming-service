<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EquipmentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $equipmentTypes = EquipmentType::all();
            $formatter = $equipmentTypes->map(function ($equipmentType) {
                return [
                    'id' => $equipmentType->id,
                    'equipment_type_id' => $equipmentType->equipment_id,
                    'equipment_name' => $equipmentType->equipment->name,
                    'requires_tractor' => $equipmentType->requires_tractor,
                    // 'is_self_propelled' => $equipmentType->is_self_propelled,
                    'minutes_per_kanal' => $equipmentType->minutes_per_kanal,
                    'price_per_kanal' => $equipmentType->price_per_kanal,
                    'min_kanal' => $equipmentType->min_kanal,
                    'image' => $equipmentType->image,
                    'service_id' => $equipmentType->service_id,
                    'service_name' => $equipmentType->service->name,
                ];
            });
            return $this->responseWithSuccess($formatter, 'Equipment Types fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Types not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipments,id|unique:equipment_types,equipment_id|max:155',
            'service_id' => 'required|exists:services,id',
            'requires_tractor' => 'required|boolean',
            // 'is_self_propelled' => 'required|boolean',
            'minutes_per_kanal' => 'required|integer',
            'price_per_kanal' => 'required|numeric',
            'min_kanal' => 'required|integer',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        try {

            $equipmentName = Equipment::findOrFail($request->equipment_id)->name;

            $path = $request->file('image')->store("equipments/{$equipmentName}", 's3');

            /** @var \Illuminate\Contracts\Filesystem\Cloud $disk */
            $disk = Storage::disk('s3');
            $url = $disk->url($path);

            $data = EquipmentType::create([
                'equipment_id' => $request->equipment_id,
                'service_id' => $request->service_id,
                'requires_tractor' => $request->requires_tractor,
                // 'is_self_propelled' => $request->is_self_propelled,
                'minutes_per_kanal' => $request->minutes_per_kanal,
                'price_per_kanal' => $request->price_per_kanal,
                'min_kanal' => $request->min_kanal,
                'image' => $url
            ]);

            return $this->responseWithSuccess($data, 'Equipment Type created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Type not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $equipmentType = EquipmentType::findOrFail($id);
            $formatter = [
                'id' => $equipmentType->id,
                'name' => $equipmentType->name,
                'requires_tractor' => $equipmentType->requires_tractor,
                // 'is_self_propelled' => $equipmentType->is_self_propelled,
                'minutes_per_kanal' => $equipmentType->minutes_per_kanal,
                'price_per_kanal' => $equipmentType->price_per_kanal,
                'min_kanal' => $equipmentType->min_kanal,
                'image' => $equipmentType->image,
                'service_id' => $equipmentType->service_id,
                'service_name' => $equipmentType->service->name,
            ];
            return $this->responseWithSuccess($formatter, 'Equipment Type fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Type not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipments,id|unique:equipment_types,equipment_id,' . $id . ',id|string|max:255',
            'service_id' => 'required|exists:services,id',
            'requires_tractor' => 'required|boolean',
            // 'is_self_propelled' => 'required|boolean',
            'minutes_per_kanal' => 'required|integer',
            'price_per_kanal' => 'required|numeric',
            'min_kanal' => 'required|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        try {

            $equipment = EquipmentType::find($id);

            $equipmentName = Equipment::findOrFail($request->equipment_id)->name;

            $data = $request->except('image');

            if ($request->hasFile('image')) {
                if ($equipment->image) {
                    $oldPath = ltrim(parse_url($equipment->image, PHP_URL_PATH), '/');
                    $disk = Storage::disk('s3');
                    if ($disk->exists($oldPath)) {
                        $disk->delete($oldPath);
                    }
                }
                $path = $request->file('image')->store("equipments/{$equipmentName}", 's3');
                /**
                 * @var \Illuminate\Filesystem\AwsS3V3Adapter|\Illuminate\Contracts\Filesystem\Cloud $disk
                 */
                $disk = Storage::disk('s3');
                $data['image'] = $disk->url($path);
            }


            $equipment->update($data);
            return $this->responseWithSuccess($data, 'Equipment Type updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Type not found');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $equipment = EquipmentType::findOrFail($id);

            if ($equipment->image) {

                $oldPath = ltrim(parse_url($equipment->image, PHP_URL_PATH), '/');
                $disk = Storage::disk('s3');

                if ($disk->exists($oldPath)) {

                    $disk->delete($oldPath);

                }
            }

            $equipment->delete();

            return $this->responseWithSuccess(null, 'Equipment Type deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Type not found');
        }
    }
}
