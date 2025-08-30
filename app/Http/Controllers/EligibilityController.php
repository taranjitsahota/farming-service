<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\PartnerAreaCoverage;
use App\Models\ServiceArea;
use App\Services\InterestedUsers\InterestedUsers;
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
        try {
            $request->validate([
                'state_id'   => 'required|exists:states,id|integer',
                'tehsil_id'  => 'required|exists:tehsils,id|integer',
                'district_id' => 'required|exists:districts,id|integer',
                'village'    => 'required|exists:villages,id|integer',
            ]);

            // Find the area for this village
            $area = Area::withoutGlobalScopes()
                ->where('village_id', $request->village)
                ->first();

            if (!$area) {
                // still track interested users
                app(InterestedUsers::class)->createInterestedUser(
                    $request->user(),
                    [
                        'state_id'   => $request->state_id,
                        'district_id' => $request->district_id,
                        'tehsil_id'  => $request->tehsil_id,
                        'village_id' => $request->village,
                    ]
                );

                return $this->responseWithSuccess(
                    ['available' => false],
                    'Service not available in this area',
                    200
                );
            }

            $serviceArea = PartnerAreaCoverage::withoutGlobalScopes()
                ->where('area_id', $area->id)
                ->where('is_enabled', true)
                ->first();

            if (!$serviceArea) {
                app(InterestedUsers::class)->createInterestedUser(
                    $request->user(),
                    [
                        'state_id'   => $request->state_id,
                        'district_id' => $request->district_id,
                        'tehsil_id'  => $request->tehsil_id,
                        'village_id' => $request->village,
                    ]
                );

                return $this->responseWithSuccess(
                    ['available' => false],
                    'Service not available in this area',
                    200
                );
            }

            $data = [
                'available'     => true,
                'substation_id' => $area->substation_id,
                'area_id'       => $serviceArea->area_id
            ];

            return $this->responseWithSuccess($data, 'Service availability checked successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Unexpected error occurred');
        }
    }
}
