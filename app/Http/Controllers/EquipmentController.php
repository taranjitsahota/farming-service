<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\PartnerAreaCoverage;
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
            $equipment = Equipment::with('service')->get();
            $formatter = $equipment->map(function ($equipment) {
                return [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    // 'substation_name' => $equipment->substation->name,
                    // 'substation_id' => $equipment->substation->id,
                    'service_id' => $equipment->service_id,
                    'service_name' => $equipment->service->name,
                    // 'is_enabled' => $equipment->is_enabled,
                    // 'image' => $equipment->image,
                    // 'price_per_kanal' => $equipment->price_per_kanal,
                    // 'min_kanal' => $equipment->min_kanal,
                    // 'minutes_per_kanal' => $equipment->minutes_per_kanal,
                    // 'inventory' => $equipment->inventory
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
                'name' => 'required|string|unique:equipments,name|max:255',
                // 'substation_id' => 'required|exists:substations,id',
                'service_id' => 'required|exists:services,id',
                // 'price_per_kanal' => 'required|numeric',
                // 'min_kanal' => 'required|integer',
                // 'is_enabled' => 'required|boolean',
                // 'minutes_per_kanal' => 'required|integer',
                // 'inventory' => 'required|integer',
                // 'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            ]);

            // $exists = Equipment::where('name', $request->name)->where('substation_id', $request->substation_id)->exists();
            // if ($exists) {
            //     return $this->responseWithError('Equipment already exists', 422, 'equipment already exists');
            // }

            // $path = $request->file('image')->store("equipments/{$request->name}", 's3');

            /** @var \Illuminate\Contracts\Filesystem\Cloud $disk */
            // $disk = Storage::disk('s3');
            // $url = $disk->url($path);

            $equipment = Equipment::create([
                'name' => $request->name,
                // 'substation_id' => $request->substation_id,
                'service_id' => $request->service_id,
                'service_name' => $request->service_name,
                // 'price_per_kanal' => $request->price_per_kanal,
                // 'min_kanal' => $request->min_kanal,
                // 'is_enabled' => $request->is_enabled,
                // 'minutes_per_kanal' => $request->minutes_per_kanal,
                // 'inventory' => $request->inventory,
                // 'image' => $url,
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
                'name' => 'sometimes|required|unique:equipments,name,' . $id . ',id|string|max:255',
                // 'price_per_kanal' => 'sometimes|required|numeric',
                // 'min_kanal' => 'sometimes|required|integer',
                // 'substation_id' => 'sometimes|required|exists:substations,id',
                'service_id' => 'sometimes|required|exists:services,id',
                // 'is_enabled' => 'required',
                // 'minutes_per_kanal' => 'sometimes|required|integer',
                // 'inventory' => 'sometimes|required|integer',
                // 'image' => 'sometimes|file|mimes:jpg,jpeg,png|max:2048',
            ]);

            // $exists = Equipment::where('name', $request->name)->where('substation_id', $request->substation_id)->where('id', '!=', $id)->exists();
            // if ($exists) {
            //     return $this->responseWithError('Equipment already exists', 422, 'equipment already exists');
            // }
            $equipment = Equipment::findOrFail($id);

            // $data = $request->except('image');

            // if ($request->hasFile('image')) {
            //     if ($equipment->image) {
            //         $oldPath = ltrim(parse_url($equipment->image, PHP_URL_PATH), '/');
            //         Storage::disk('s3')->delete($oldPath);
            //     }
            //     $path = $request->file('image')->store("equipments/{$request->name}", 's3');
            //     /**
            //      * @var \Illuminate\Filesystem\AwsS3V3Adapter|\Illuminate\Contracts\Filesystem\Cloud $disk
            //      */
            //     $disk = Storage::disk('s3');
            //     $data['image'] = $disk->url($path);
            // }

            $equipment->update($request->all());
            return $this->responseWithSuccess($equipment, 'equipment updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422, 'equipment not updated');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'equipment not updated');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $equipment = Equipment::findOrFail($id);

            // if ($equipment->image) {
            //     $oldPath = ltrim(parse_url($equipment->image, PHP_URL_PATH), '/');
            //     Storage::disk('s3')->delete($oldPath);
            // }

            $equipment->delete();
            return $this->responseWithSuccess(null, 'equipment deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'equipment not found', 404);
        }
    }
    public function EquipmentByServiceId($serviceId)
    {
        try {
            $equipments = Equipment::where('service_id', $serviceId)
                ->select('id', 'name', 'service_id')
                ->get();

            $formatter = $equipments->map(function ($equipment) {
                return [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'service_id' => $equipment->service_id,
                ];
            });

            return $this->responseWithSuccess($formatter, 'Equipments fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
    // public function getEquipmentByVillage(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'village_id' => 'required|exists:villages,id|integer',
    //         ]);

    //         // 1. Get the area for this village
    //         $area = Area::withoutGlobalScopes()
    //             ->where('village_id', $request->village_id)
    //             // ->where('is_enabled', true)
    //             ->first();

    //         if (!$area) {
    //             return $this->responseWithSuccess(
    //                 ['available' => false, 'equipments' => []],
    //                 'Service not available in this area',
    //                 200
    //             );
    //         }

    //         // 2. Get equipment via ServiceAreas for this Area
    //         $equipment = Equipment::whereHas('serviceArea', function ($query) use ($area) {
    //             $query->where('area_id', $area->id);
    //             // ->where('is_enabled', true);
    //         })
    //             ->with('substation', 'service')
    //             ->get();

    //         if ($equipment->isEmpty()) {
    //             return $this->responseWithSuccess(
    //                 ['available' => true, 'equipments' => []],
    //                 'No equipment available in this area',
    //                 200
    //             );
    //         }

    //         // 3. Format response
    //         $formatter = $equipment->map(function ($eq) {
    //             return [
    //                 'id'              => $eq->id,
    //                 'name'            => $eq->name,
    //                 'substation_id'   => $eq->substation->id ?? null,
    //                 'substation_name' => $eq->substation->name ?? null,
    //                 'service_id'      => $eq->service_id,
    //                 'service_name'    => $eq->service->name ?? null,
    //                 // 'is_enabled'      => $eq->is_enabled,
    //                 'image'           => $eq->image,
    //                 'price_per_kanal' => $eq->price_per_kanal,
    //                 'min_kanal'       => $eq->min_kanal,
    //                 'minutes_per_kanal' => $eq->minutes_per_kanal,
    //                 'inventory'       => $eq->inventory,
    //                 'service_area_id' => $eq->serviceArea->first()->id
    //             ];
    //         });

    //         return $this->responseWithSuccess(
    //             ['available' => true, 'equipments' => $formatter],
    //             'Equipment fetched successfully',
    //             200
    //         );
    //     } catch (\Exception $e) {
    //         return $this->responseWithError($e->getMessage(), 500, 'Unexpected error occurred');
    //     }
    // }

    public function getEquipmentsByAreaAndService($areaId, $serviceId)
    {
        try {
            // Find partners covering this area
            $partnerIds = PartnerAreaCoverage::where('area_id', $areaId)
                ->where('is_enabled', true)
                ->pluck('partner_id');

            if ($partnerIds->isEmpty()) {
                return $this->responseWithSuccess([], 'No equipments available in this area', 200);
            }

            // Fetch equipments for those partners and service
            $equipmentTypes = EquipmentType::where('service_id', $serviceId)
                ->with(['units' => function ($q) use ($partnerIds) {
                    if ($partnerIds->isNotEmpty()) {
                        $q->whereIn('partner_id', $partnerIds)
                            ->where('status', 'active');
                    }
                }])
                ->get();
            $formatter = $equipmentTypes->map(function ($eq) {
                return [
                    'id'              => $eq->id,
                    'name'            => $eq->equipment->name,
                    'service_id'      => $eq->service_id,
                    'image'           => $eq->image,
                    'price_per_kanal' => $eq->price_per_kanal,
                    'min_kanal'       => $eq->min_kanal,
                    'minutes_per_kanal' => $eq->minutes_per_kanal,
                    'requires_tractor' => $eq->requires_tractor,
                    'is_available'      => $eq->units->isNotEmpty(),
                ];
            });


            return $this->responseWithSuccess($formatter, 'Equipments fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Unexpected error occurred');
        }
    }
}
