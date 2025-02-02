<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
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
                'service_name' => 'required|string|max:255',
                'description'  => 'nullable|string|max:1000',
                'price'         => 'required|numeric',
                'is_enabled'    => 'boolean'
            ]);

            $service = Service::create($validated);

            return response()->json([
                'message' => 'Service created successfully',
                'service' => $service
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error'   => $e->getMessage()
            ], 500);
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

            return response()->json([
                'service' => $service
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Service not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error'   => $e->getMessage()
            ], 500);
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
                'service_name' => 'sometimes|string|max:255',
                'description'  => 'sometimes|string|max:1000',
                'price'        => 'sometimes|numeric',
                'is_enabled'   => 'sometimes|boolean'
            ]);

            $service->update($validated);

            return response()->json([
                'message' => 'Service updated successfully',
                'service' => $service
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Service not found'
            ], 404);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error'   => $e->getMessage()
            ], 500);
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

            return response()->json([
                'message' => 'Service deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Service not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

   

}
