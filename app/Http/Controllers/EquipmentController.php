<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $equipment = Equipment::with('substation', 'service')->get();
            $formatter = $equipment->map(function ($equipment) {
                return [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'substation_name' => $equipment->substation->name,
                    'substation_id' => $equipment->substation->id,
                    'service_id' => $equipment->service_id,
                    'service_name' => $equipment->service->name,
                    'is_enabled' => $equipment->is_enabled,
                    'image' => $equipment->image,
                    'price_per_kanal' => $equipment->price_per_kanal,
                    'min_kanal' => $equipment->min_kanal,
                    'minutes_per_kanal' => $equipment->minutes_per_kanal,
                    'inventory' => $equipment->inventory


                ];
            });

            return $this->responseWithSuccess($formatter, 'equipment fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'equipment not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'substation_id' => 'required|exists:substations,id',
                'service_id' => 'required|exists:services,id',
                'price_per_kanal' => 'required|numeric',
                'min_kanal' => 'required|integer',
                'is_enabled' => 'required|boolean',
                'minutes_per_kanal' => 'required|integer',
                'inventory' => 'required|integer',
                'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            ]);

            $exists = Equipment::where('name', $request->name)->where('substation_id', $request->substation_id)->exists();
            if ($exists) {
                return $this->responseWithError('Equipment already exists', 422, 'equipment already exists');
            }

            $path = $request->file('image')->store("equipments/{$request->name}", 's3');

            /** @var \Illuminate\Contracts\Filesystem\Cloud $disk */
            $disk = Storage::disk('s3');
            $url = $disk->url($path);

            $equipment = Equipment::create([
                'name' => $request->name,
                'substation_id' => $request->substation_id,
                'service_id' => $request->service_id,
                'service_name' => $request->service_name,
                'price_per_kanal' => $request->price_per_kanal,
                'min_kanal' => $request->min_kanal,
                'is_enabled' => $request->is_enabled,
                'minutes_per_kanal' => $request->minutes_per_kanal,
                'inventory' => $request->inventory,
                'image' => $url,
            ]);

            return $this->responseWithSuccess($equipment, 'equipment created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 'equipment not updated', 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'equipment not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $equipment = Equipment::findOrFail($id);
            return $this->responseWithSuccess($equipment, 'equipment fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'equipment not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'price_per_kanal' => 'sometimes|required|numeric',
                'min_kanal' => 'sometimes|required|integer',
                'substation_id' => 'sometimes|required|exists:substations,id',
                'service_id' => 'sometimes|required|exists:services,id',
                'is_enabled' => 'required',
                'minutes_per_kanal' => 'sometimes|required|integer',
                'inventory' => 'sometimes|required|integer',
                'image' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
            ]);

            $exists = Equipment::where('name', $request->name)->where('substation_id', $request->substation_id)->where('id', '!=', $id)->exists();
            if ($exists) {
                return $this->responseWithError('Equipment already exists', 422, 'equipment already exists');
            }
            $equipment = Equipment::findOrFail($id);

            $data = $request->except('image');

            if ($request->hasFile('image')) {
                if ($equipment->image) {
                    $oldPath = ltrim(parse_url($equipment->image, PHP_URL_PATH), '/');
                    Storage::disk('s3')->delete($oldPath);
                }
                $path = $request->file('image')->store("equipments/{$request->name}", 's3');
                /**
                 * @var \Illuminate\Filesystem\AwsS3V3Adapter|\Illuminate\Contracts\Filesystem\Cloud $disk
                 */
                $disk = Storage::disk('s3');
                $data['image'] = $disk->url($path);
            }

            $equipment->update($data);
            return $this->responseWithSuccess($equipment, 'equipment updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 'equipment not updated', 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'equipment not updated', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $equipment = Equipment::findOrFail($id);

            if ($equipment->image) {
                $oldPath = ltrim(parse_url($equipment->image, PHP_URL_PATH), '/');
                Storage::disk('s3')->delete($oldPath);
            }

            $equipment->delete();
            return $this->responseWithSuccess(null, 'equipment deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'equipment not found', 404);
        }
    }
}
