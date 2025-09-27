<?php

namespace App\Http\Controllers;

use App\Models\EquipmentUnavailability;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EquipmentUnavailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $equipmentUnavailability = EquipmentUnavailability::with('unit', 'unit.equipmentType', 'unit.partner')->get();
            $formatter = $equipmentUnavailability->map(function ($item) {
                return [
                    'id' => $item->id,
                    'unit_id' => $item->unit_id,
                    'unit_name' => $item->unit->name,
                    'serial_no' => $item->unit->serial_no,
                    'equipment_type_id' => $item->unit->equipment_type_id,
                    'equipment_type_name' => $item->unit->equipmentType->equipment->name,
                    // 'substation_id' => $item->unit->substation_id,
                    // 'substation_name' => $item->unit->substation->name,
                    'partner_id' => $item->unit->partner_id,
                    'partner_name' => $item->unit->partner->user->name,
                    'start_at' => $item->start_at->format('Y-m-d'),
                    'end_at' => $item->end_at->format('Y-m-d'),
                    'leave_type' => $item->leave_type,
                    'shift' => $item->shift,
                    'reason' => $item->reason,
                ];
            });
            return $this->responseWithSuccess($formatter, 'Equipment Unavailability fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unavailability not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'unit_id' => 'required|exists:equipment_units,id',
            'shift' => 'required_if:leave_type,shift|nullable|in:first,second',
            'leave_type' => 'required|in:single_day,shift,long_leave',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'reason' => 'required|string',
        ]);
        try {
            $equipmentUnavailability = EquipmentUnavailability::where('unit_id', $request->unit_id)
                ->where(function ($query) use ($request) {
                    $query->where('start_at', '<=', $request->start_at)
                        ->where('end_at', '>=', $request->start_at);
                })
                ->first();

            if ($equipmentUnavailability) {
                return $this->responseWithError('Equipment Unavailability already exists', 500, 'Equipment Unavailability not created');
            }
            $data = EquipmentUnavailability::create($request->all());
            return $this->responseWithSuccess($data, 'Equipment Unavailability created successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 500, 'Equipment Unavailability not created');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unavailability not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $equipmentUnavailability = EquipmentUnavailability::with('unit', 'unit.equipmentType', 'unit.substation', 'unit.tractor', 'unit.partner')->find($id);
            $formatter = [
                'id' => $equipmentUnavailability->id,
                'unit_id' => $equipmentUnavailability->unit_id,
                'unit_name' => $equipmentUnavailability->unit->name,
                'equipment_type_id' => $equipmentUnavailability->unit->equipment_type_id,
                'equipment_type_name' => $equipmentUnavailability->unit->equipmentType->name,
                'substation_id' => $equipmentUnavailability->unit->substation_id,
                'substation_name' => $equipmentUnavailability->unit->substation->name,
                'tractor_id' => $equipmentUnavailability->unit->tractor_id ?? null,
                'tractor_name' => $equipmentUnavailability->unit->tractor->name,
                'partner_id' => $equipmentUnavailability->unit->partner_id,
                'partner_name' => $equipmentUnavailability->unit->partner->name,
                'start_at' => $equipmentUnavailability->start_at,
                'end_at' => $equipmentUnavailability->end_at,
                'reason' => $equipmentUnavailability->reason,
            ];
            return $this->responseWithSuccess($formatter, 'Equipment Unavailability fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unavailability not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'unit_id' => 'required|exists:equipment_units,id',
            'partner_id' => 'required|exists:partners,id',
            'leave_type' => 'required|in:single_day,shift,long_leave',
            'shift' => 'required_if:leave_type,shift|nullable|in:first,second',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'reason' => 'required|string',
        ]);
        try {

            $startDate = Carbon::parse($request->start_at)->startOfDay();
            $endDate   = Carbon::parse($request->end_at)->endOfDay();

            $exists = EquipmentUnavailability::where('unit_id', $request->unit_id)
                ->where('id', '!=', $id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_at', '<=', $endDate)
                        ->where('end_at', '>=', $startDate);
                })
                ->exists();

            if ($exists) {
                return $this->responseWithError('Equipment Unavailability already exists', 422);
            }

            $equipmentUnavailability = EquipmentUnavailability::find($id);
            $equipmentUnavailability->update($request->all());
            return $this->responseWithSuccess($equipmentUnavailability, 'Equipment Unavailability updated successfully', 200);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 500, 'Equipment Unavailability not updated');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unavailability not updated');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $equipmentUnavailability = EquipmentUnavailability::find($id);
            $equipmentUnavailability->delete();
            return $this->responseWithSuccess($equipmentUnavailability, 'Equipment Unavailability deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Equipment Unavailability not deleted');
        }
    }
}
