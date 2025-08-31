<?php

namespace App\Http\Controllers;

use App\Models\PartnerAreaCoverage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PartnerAreaCoverageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $partnerAreaCoverages = PartnerAreaCoverage::with('partner', 'area', 'area.tehsil', 'area.district', 'area.state', 'area.village')->get();
            $formatter = $partnerAreaCoverages->map(function ($item) {
                // $unit = $item->equipmentType->units()
                //     ->where('partner_id', $item->partner_id)z
                //     ->first();

                return [
                    'id' => $item->id,
                    'partner_id' => $item->partner_id,
                    'user_name' => $item->partner->user->name,
                    'area_id' => $item->area_id,
                    'tehsil_id' => $item->area->tehsil_id,
                    'tehsil_name' => $item->area->tehsil->name,
                    'district_id' => $item->area->district_id,
                    'district_name' => $item->area->district->name,
                    'state_id' => $item->area->state_id,
                    'state_name' => $item->area->state->name,
                    'village_id' => $item->area->village_id,
                    'village_name' => $item->area->village->name,
                    'area_name' => $item->area->state->name . ' > ' .
                        $item->area->district->name . ' > ' .
                        $item->area->tehsil->name . ' > ' .
                        $item->area->village->name,
                    // 'equipment_type_id' => $item->equipment_type_id,
                    // 'equipment_type_name' => $item->equipmentType->equipment->name,
                    //    'label' => $item->equipmentType->equipment->name . 
                //    ($unit ? ' (' . $unit->serial_no . ')' : ''),
                    'is_enabled' => $item->is_enabled
                ];
            });
            return $this->responseWithSuccess($formatter, 'Partner Area Coverage fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Area Coverage not found');
        }
    }
    // 'label' => $unit->equipmentType->name
    //                         . ($unit->serial_no ? " (Serial: {$unit->serial_no})" : "")
    //                         . ($unit->substation ? " – {$unit->substation->name}" : ""),
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required',
            'area_id' => 'required',
            // 'equipment_type_id' => 'required',
            'is_enabled' => 'required|boolean'
        ]);
        try {
            $exists = PartnerAreaCoverage::where('partner_id', $request->partner_id)
                ->where('area_id', $request->area_id)
                // ->where('equipment_type_id', $request->equipment_type_id)
                ->exists();

            if ($exists) {
                return $this->responseWithError('Partner Area Coverage already exists', 500, 'Partner Area Coverage not created');
            }
            $data = PartnerAreaCoverage::create($request->all());
            return $this->responseWithSuccess($data, 'Partner Area Coverage created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 500, 'Partner Area Coverage not created');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Area Coverage not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $partnerAreaCoverage = PartnerAreaCoverage::with('partner', 'area', 'area.tehsil', 'area.district', 'area.state', 'area.village')->find($id);
            $formatter = [
                'id' => $partnerAreaCoverage->id,
                'partner_id' => $partnerAreaCoverage->partner_id,
                'user_name' => $partnerAreaCoverage->partner->name,
                'area_id' => $partnerAreaCoverage->area_id,
                'tehsil_id' => $partnerAreaCoverage->area->tehsil_id,
                'tehsil_name' => $partnerAreaCoverage->area->tehsil->name,
                'district_id' => $partnerAreaCoverage->area->district_id,
                'district_name' => $partnerAreaCoverage->area->district->name,
                'state_id' => $partnerAreaCoverage->area->state_id,
                'state_name' => $partnerAreaCoverage->area->state->name,
                'village_id' => $partnerAreaCoverage->area->village_id,
                'village_name' => $partnerAreaCoverage->area->village->name,
                // 'equipment_type_id' => $partnerAreaCoverage->equipment_type_id,
                // 'equipment_type_name' => $partnerAreaCoverage->equipmentType->name,
                'is_enabled' => $partnerAreaCoverage->is_enabled
            ];
            return $this->responseWithSuccess($formatter, 'Partner Area Coverage fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Area Coverage not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'partner_id' => 'required',
            'area_id' => 'required',
            // 'equipment_type_id' => 'required',
            'is_enabled' => 'required|boolean'
        ]);
        try {
            $partnerAreaCoverage = PartnerAreaCoverage::find($id);
            $partnerAreaCoverage->update($request->all());
            return $this->responseWithSuccess($partnerAreaCoverage, 'Partner Area Coverage updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 500, 'Partner Area Coverage not updated');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Area Coverage not updated');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $partnerAreaCoverage = PartnerAreaCoverage::find($id);
            $partnerAreaCoverage->delete();
            return $this->responseWithSuccess($partnerAreaCoverage, 'Partner Area Coverage deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner Area Coverage not deleted');
        }
    }
}
