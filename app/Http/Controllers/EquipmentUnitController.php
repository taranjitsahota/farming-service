<?php

namespace App\Http\Controllers;

use App\Models\EquipmentUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EquipmentUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $equipmentUnits = EquipmentUnit::with('partner')->get();
            $formatter = $equipmentUnits->map(function ($item) {
                return [
                    'id' => $item->id,
                    'partner_id' => $item->partner_id,
                    'user_name' => $item->partner->user->name,
                    'equipment_type_id' => $item->equipment_type_id,
                    'equipment_type_name' => $item->equipmentType->equipment->name,
                    'substation_id' => $item->substation_id,
                    'substation_name' => $item->substation->name,
                    // 'tractor_id' => $item->tractor_id,
                    // 'tractor_name' => $item->tractor->name,
                    'serial_no' => $item->serial_no,
                    'status' => $item->status,
                ];
            });
            return $this->responseWithSuccess($formatter, 'Equipment Unit fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unit not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:users,id',
            'substation_id' => 'required|exists:substations,id',
            'status' => 'required',
            'equipments' => 'required|array',
            'equipments.*.equipment_type_id' => 'required|exists:equipment_types,id',
            'equipments.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $units = [];

            foreach ($request->equipments as $equipment) {
                for ($i = 0; $i < (int) $equipment['quantity']; $i++) {
                    $units[] = EquipmentUnit::create([
                        'partner_id' => $request->partner_id,
                        'substation_id' => $request->substation_id,
                        'equipment_type_id' => $equipment['equipment_type_id'],
                        'status' => $request->status,
                    ]);
                }
            }

            return $this->responseWithSuccess($units, 'Equipment Units created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unit not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $equipmentUnit = EquipmentUnit::find($id);
            $formatter = [
                'id' => $equipmentUnit->id,
                'partner_id' => $equipmentUnit->partner_id,
                'user_name' => $equipmentUnit->users->name,
                'equipment_type_id' => $equipmentUnit->equipment_type_id,
                'substation_id' => $equipmentUnit->substation_id,
                'serial_no' => $equipmentUnit->serial_no,
                'status' => $equipmentUnit->status,
            ];
            return $this->responseWithSuccess($formatter, 'Equipment Unit fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unit not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'partner_id' => 'required|exists:users,id',
            'equipment_type_id' => 'required|exists:equipment_types,id',
            'substation_id' => 'required|exists:substations,id',
            'status' => 'required',
        ]);
        try {
            $data = EquipmentUnit::find($id);
            $data->update($request->all());
            return $this->responseWithSuccess($data, 'Equipment Unit updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unit not found');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = EquipmentUnit::find($id);
            $data->delete();
            return $this->responseWithSuccess($data, 'Equipment Unit deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unit not found');
        }
    }

    public function equipmentUnitByPartnerId($id)
    {
        try {
            $equipmentUnits = EquipmentUnit::where('partner_id', $id)->with(['equipmentType', 'equipmentType.equipment', 'substation', 'tractor'])->get()->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'label' => $unit->equipmentType->equipment->name
                        . ($unit->serial_no ? " (Serial: {$unit->serial_no})" : "")
                        . ($unit->substation ? " – {$unit->substation->name}" : ""),
                ];
            });
            // $formatter = $equipmentUnits->map(function ($item) {
            //     return [
            //         'id' => $item->id,
            //         'partner_id' => $item->partner_id,
            //         'user_name' => $item->partner->name,
            //         'equipment_type_id' => $item->equipment_type_id,
            //         'equipment_type_name' => $item->equipmentType->name,
            //         'substation_id' => $item->substation_id,
            //         'substation_name' => $item->substation->name,
            //         'tractor_id' => $item->tractor_id,
            //         'tractor_name' => $item->tractor->name,
            //         'serial_no' => $item->serial_no,
            //         'status' => $item->status,
            //     ];
            // });
            return $this->responseWithSuccess($equipmentUnits, 'Equipment Unit fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unit not found');
        }
    }
}
