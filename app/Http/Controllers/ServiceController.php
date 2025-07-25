<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{

    public function index(){
        try{
            $services = Service::with('equipment', 'substation')->get();

            $formatted = $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'equipment_name' => $service->equipment->name,
                    'equipment_id' => $service->equipment_id ?? null,
                    'substation_name' => $service->substation->name ?? null,
                    'substation_id' => $service->substation_id,
                    'category' => $service->category,
                    'is_enabled' => $service->is_enabled
                ];
            });

            return $this->responseWithSuccess($formatted, 'Services fetched successfully', 200);

        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
    /**
     * Store a newly created service in storage.
     * 
     * @OA\Post(
     *     path="/api/services",
     *     summary="Add a new service",
     *     description="Create a new service with name, description, price, and enabled status.",
     *     tags={"Services"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"service_name", "price"},
     *             @OA\Property(property="service_name", type="string", example="Lawn Mowing"),
     *             @OA\Property(property="description", type="string", example="A basic lawn mowing service"),
     *             @OA\Property(property="price", type="number", format="float", example=50.00),
     *             @OA\Property(property="is_enabled", type="boolean", example=true)
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
            $validated = $request->validate([
                'equipment_id' => 'required|exists:equipments,id',
                'substation_id' => 'required|exists:substations,id',
                'category' => 'required|string|max:255',
                'is_enabled'    => 'boolean'
            ]);

            $service = Service::create($validated);

            return $this->responseWithSuccess($service, 'Service created successfully', 201);
           

        } catch (ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());

        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

/**
     * Display the specified service.
     * 
     * @OA\Get(
     *     path="/api/services/{id}",
     *     summary="Display a specific service",
     *     description="Get the details of a specific service.",
     *     tags={"Services"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function show(string $id)
    {
        try {
            $service = Service::findOrFail($id);

            return $this->responseWithSuccess($service, 'Service fetched successfully', 200);
            

        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified service in storage.
     * 
     * @OA\Put(
     *     path="/api/services/{id}",
     *     summary="Update a service",
     *     description="Update the details of an existing service.",
     *     tags={"Services"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"service_name", "price"},
     *             @OA\Property(property="service_name", type="string", example="Lawn Mowing"),
     *             @OA\Property(property="description", type="string", example="Updated description of service"),
     *             @OA\Property(property="price", type="number", format="float", example=60.00),
     *             @OA\Property(property="is_enabled", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $service = Service::findOrFail($id);

            $validated = $request->validate([
                // 'equipment_id' => 'required|exists:equipments,id',
                // 'category' => 'required|string|max:255',
                'is_enabled'    => 'required|boolean'
            ]);

            $service->update($request->all());

            return $this->responseWithSuccess($service, 'Service updated successfully', 200);

        } catch (ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());

        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified service from storage.
     * 
     * @OA\Delete(
     *     path="/api/services/{id}",
     *     summary="Delete a service",
     *     description="Remove a service from the database.",
     *     tags={"Services"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function destroy(string $id)
    {
        try {
            $service = Service::findOrFail($id);
            $service->delete();

            return $this->responseWithSuccess([], 'Service deleted successfully', 200);
          

        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

   public function ServiceByEquipmentId($equipmentId)
    {
        try {
            $services = Service::where('equipment_id', $equipmentId)->get();

            return $this->responseWithSuccess($services, 'Services fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

}
