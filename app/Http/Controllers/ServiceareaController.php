<?php

namespace App\Http\Controllers;

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
        return $this->responseWithSuccess(ServiceArea::all(), 'Service areas fetched successfully', 200);
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
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'area_id'    => 'required|exists:areas,id',
            ]);

            // Create the service area entry
            $serviceArea = ServiceArea::create($validated);

            return $this->responseWithSuccess($serviceArea, 'Service area created successfully', 201);

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
                'area_id'    => 'sometimes|exists:areas,id',
            ]);
    
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
        try{
        $serviceArea = ServiceArea::findOrFail($id);
        $serviceArea->delete();
        return $this->responseWithSuccess([], 'Service area deleted successfully', 200);

        }catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
