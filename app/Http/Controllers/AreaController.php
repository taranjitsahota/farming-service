<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *     path="/api/areas",
     *     summary="Get all areas",
     *     tags={"Areas"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     * )
     */

    public function index()
    {
        try {
            $areas = Area::with(['state', 'district', 'tehsil', 'village', 'substation'])->get();

            // Flatten the data
            $formatted = $areas->map(function ($area) {
                return [
                    'id'           => $area->id,
                    'village_id'   => $area->village_id,
                    'village_name' => $area->village?->name ?? null,
                    'tehsil_id'      => $area->tehsil_id,
                    'tehsil_name'    => $area->tehsil?->name ?? null,
                    'district_id'    => $area->district_id,
                    'district_name'  => $area->district?->name ?? null,
                    'state_id'     => $area->state_id,
                    'state_name'   => $area->state?->name ?? null,
                    'substation_id' => $area->substation_id ?? null,
                    'substation_name' => $area->substation?->name ?? null,
                    'pincode'      => $area->pincode,
                    // 'is_enabled'   => $area->is_enabled,
                ];
            });

            return $this->responseWithSuccess($formatted, 'Areas retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }



    /**
     * Store a newly created area in storage.
     * 
     * @OA\Post(
     *     path="/api/areas",
     *     summary="Add a new area",
     *     description="Create a new area with tehsil,district, state, village, pincode, and enabled status.",
     *     tags={"Areas"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tehsil_id", "state_id", "pincode"},
     *             @OA\Property(property="tehsil_id", type="integer", example=1),
     *             @OA\Property(property="district_id", type="integer", example=1),
     *             @OA\Property(property="state_id", type="integer", example=2),
     *             @OA\Property(property="village", type="string", example="Greenfield"),
     *             @OA\Property(property="pincode", type="string", example="123456"),
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
            $request->validate([
                'state_id' => 'required|exists:states,id',
                'district_id' => 'required|exists:districts,id',
                'tehsil_id' => 'required|exists:tehsils,id',
                'village_ids' => 'required|array',
                'village_ids.*' => 'exists:villages,id',
                'substation_id' => 'nullable|exists:substations,id',
            ]);

            $createdAreas = [];
            foreach ($request->village_ids as $villageId) {
                // prevent duplicates
                $exists = Area::where('state_id', $request->state_id)
                    ->where('district_id', $request->district_id)
                    ->where('tehsil_id', $request->tehsil_id)
                    ->where('village_id', $villageId)
                    ->first();

                if (!$exists) {
                    $createdAreas[] = Area::create([
                        'state_id' => $request->state_id,
                        'district_id' => $request->district_id,
                        'tehsil_id' => $request->tehsil_id,
                        'village_id' => $villageId,
                        'substation_id' => $request->substation_id ?? null,
                    ]);
                }
            }

            return $this->responseWithSuccess($createdAreas, 'Areas created successfully', 201);
        } catch (ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *     path="/api/areas/{id}",
     *     summary="Get a specific area",
     *     tags={"Areas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Area ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function show($id)
    {
        try {
            $area = Area::findOrFail($id);
            return $this->responseWithSuccess($area, 'Area fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/areas/{id}",
     *     summary="Update an existing area",
     *     tags={"Areas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Area ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="pincode", type="string", example="654321"),
     *             @OA\Property(property="village", type="string", example="Updated Village"),
     *             @OA\Property(property="is_enabled", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'tehsil_id'   => 'sometimes|exists:tehsils,id',
                'state_id'  => 'sometimes|exists:states,id',
                'village_id'   => 'sometimes|exists:villages,id',
                'substation_id'   => 'sometimes|exists:substations,id',
                // 'is_enabled' => 'sometimes'
            ]);

            $exists = Area::where('state_id', $request->state_id)
                ->where('district_id', $request->district_id)
                ->where('tehsil_id', $request->tehsil_id)
                ->where('village_id', $request->village_id)
                ->where('substation_id', $request->substation_id)
                ->where('id', '!=', $id)
                ->first();

            if ($exists) {
                return $this->responseWithError('Area already exists', 409);
            }

            $area = Area::findOrFail($id);
            $area->update($request->all());
            return $this->responseWithSuccess($area, 'Area updated successfully', 200);
        } catch (ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/areas/{id}",
     *     summary="Soft delete an area",
     *     tags={"Areas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Area ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function destroy($id)
    {
        try {
            $area = Area::findOrFail($id);
            $area->delete();
            return $this->responseWithSuccess([], 'Area deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    public function areaBySubstationId($substation_id)
    {
        try {
            $area = Area::with(['state', 'district', 'tehsil', 'village', 'substation'])->where('substation_id', $substation_id)->get();
            $formatted = $area->map(function ($area) {
                return [
                    'id'           => $area->id,
                    'village_id'   => $area->village_id,
                    'village_name' => $area->village?->name ?? null,
                    'tehsil_id'      => $area->tehsil_id,
                    'tehsil_name'    => $area->tehsil?->name ?? null,
                    'district_id'    => $area->district_id,
                    'district_name'  => $area->district?->name ?? null,
                    'state_id'     => $area->state_id,
                    'state_name'   => $area->state?->name ?? null,
                    'substation_id' => $area->substation_id ?? null,
                    'substation_name' => $area->substation?->name ?? null,
                    'pincode'      => $area->pincode,
                    // 'is_enabled'   => $area->is_enabled,
                ];
            });
            return $this->responseWithSuccess($formatted, 'Area fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
