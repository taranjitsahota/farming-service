<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\ServiceArea;
use Illuminate\Http\Request;

class EligibilityController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/check-eligibility",
     *     summary="Check if a service is available in a given area",
     *     description="Checks if a service is available in a given area based on the pincode and service ID. Requires authentication and a completed profile.",
     *     tags={"Service Eligibility"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"pincode", "service_id"},
     *             @OA\Property(property="pincode", type="string", example="123456"),
     *             @OA\Property(property="service_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500"),
     * )
     */


    public function checkServiceAvailability(Request $request)
    {
        // dd(1);
        try {
            $request->validate([
                'village' => 'required|integer',
                'service_id' => 'required|integer'
            ]);

            // Find the area for this village
            $area = Area::where('village_id', $request->village)
                ->where('is_enabled', true)
                ->first();

            if (!$area) {
                return $this->responseWithError('Service not available in this area', 401);
            }

            // Find service-area mapping (this also has substation_id now)
            $serviceArea = ServiceArea::where('area_id', $area->id)
                ->where('service_id', $request->service_id)
                ->first();

            if (!$serviceArea) {
                return $this->responseWithError('Service not available in this area', 401);
            }

            // Get substation_id from serviceArea
            $substationId = $serviceArea->substation_id;

            $data = [
                'substation_id' => $substationId,
                'available' => true
            ];

            return $this->responseWithSuccess($data, 'Service availability checked successfully', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
