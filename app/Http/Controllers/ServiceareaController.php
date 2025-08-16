<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\ServiceArea;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Service Areas",
 *     description="APIs for managing serviceable areas for services"
 * )
 */
class ServiceAreaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/service-areas",
     *     summary="Get all service areas",
     *     tags={"Service Areas"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function index()
    {
        try {
            $serviceAreas = ServiceArea::with('area.tehsil', 'area.district', 'area.state', 'area.village', 'service', 'equipment', 'substation')->get();

            $formatted = $serviceAreas->map(function ($serviceArea) {
                return [
                    'id' => $serviceArea->id,
                    'equipment' => $serviceArea->equipment->name,
                    'equipment_id' => $serviceArea->equipment_id,
                    'service' => $serviceArea->service?->name,
                    'service_id' => $serviceArea->service_id,
                    'area_id' => $serviceArea->area_id,
                    'tehsil' => $serviceArea->area?->tehsil?->name,
                    'district' => $serviceArea->area?->tehsil?->district?->name,
                    'state' => $serviceArea->area?->state?->name,
                    'village' => $serviceArea->area?->village?->name,
                    'substation' => $serviceArea->substation?->name,
                    'substation_id' => $serviceArea->substation_id,
                    'is_enabled' => $serviceArea->is_enabled,
                ];
            });

            return $this->responseWithSuccess($formatted, 'Service areas fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Failed to fetch service areas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/service-areas",
     *     summary="Create a new service area",
     *     tags={"Service Areas"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"service_id", "area_id"},
     *             @OA\Property(property="service_id", type="integer", example=1),
     *             @OA\Property(property="area_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=201, ref="#/components/responses/201"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'equipment_id' => 'required|exists:equipments,id',
                'service_id' => 'required|exists:services,id',
                'area_id'    => 'required|exists:areas,id',
                'substation_id' => 'required|exists:substations,id',
                'is_enabled' => 'required|boolean'
            ]);

           $serviceArea = ServiceArea::where('service_id', $request->service_id)
                ->where('equipment_id', $request->equipment_id)
                ->where('area_id', $request->area_id)
                ->where('substation_id', $request->substation_id)
                ->first();

            if ($serviceArea) {
                return $this->responseWithError('Service area already exists', 422, 'service area already exists');
            }

            // Create the service area entry
            ServiceArea::create([
                'service_id' => $request->service_id,
                'equipment_id' => $request->equipment_id,
                'area_id' => $request->area_id,
                'substation_id' => $request->substation_id,
                'is_enabled' => $request->is_enabled
            ]);

            return $this->responseWithSuccess([], 'Service area created successfully', 201);
        } catch (ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/api/service-areas/{id}",
     *     summary="Get a specific service area",
     *     tags={"Service Areas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Service Area ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function show($id)
    {
        $serviceArea = ServiceArea::findOrFail($id);
        return $this->responseWithSuccess($serviceArea, 'Service area fetched successfully', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/service-areas/{id}",
     *     summary="Update an existing service area",
     *     tags={"Service Areas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Service Area ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"service_id", "area_id"},
     *             @OA\Property(property="service_id", type="integer", example=3),
     *             @OA\Property(property="area_id", type="integer", example=4)
     *         )
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            // Find the service area, or fail with a 404 error
            $serviceArea = ServiceArea::findOrFail($id);

            // Validate the request (only validate fields that are provided)
            $validated = $request->validate([
                'service_id' => 'sometimes|exists:services,id',
                'substation_id' => 'sometimes|exists:substations,id',
                'area_id'    => 'sometimes|exists:areas,id',
                'is_enabled' => 'sometimes|boolean',
            ]);

            $serviceArea = ServiceArea::where('service_id', $request->service_id)
                ->where('equipment_id', $request->equipment_id)
                ->where('area_id', $request->area_id)
                ->where('substation_id', $request->substation_id)
                ->where('id', '!=', $id)
                ->first();

            if ($serviceArea) {
                return $this->responseWithError('Service area already exists', 422, 'service area already exists');
            }
            
            // Update only provided values
            $serviceArea->update($validated);

            return $this->responseWithSuccess($serviceArea, 'Service area updated successfully', 200);
        } catch (ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/service-areas/{id}",
     *     summary="Soft delete a service area",
     *     tags={"Service Areas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Service Area ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function destroy($id)
    {
        try {
            $serviceArea = ServiceArea::findOrFail($id);
            $serviceArea->delete();
            return $this->responseWithSuccess([], 'Service area deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    // public function checkServiceAvailability(Request $request)
    // {
    //     $request->validate([
    //         'village_id' => 'required|exists:villages,id',
    //     ]);

    //     $available = ServiceArea::where('village_id', $request->village_id)->exists();

    //     if ($available) {
    //         return $this->responseWithSuccess([], 'We are available in your village.', 200);
    //     } else {
    //         return $this->responseWithError('We re coming soon in this area', 401);
    //     }
    // }
}
