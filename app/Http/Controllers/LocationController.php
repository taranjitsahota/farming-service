<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\District;
use Illuminate\Http\Request;
use App\Models\State;
use App\Models\Tehsil;
use App\Models\ServiceArea;
use App\Models\Village;

class LocationController extends Controller
{


    public function getStates()
    {
        $data = State::select('id', 'name')->get();
        return $this->responseWithSuccess($data, 'States fetched successfully', 200);
    }

    public function getDistricts($state_id)
    {
        $data = District::select('id', 'name')->where('state_id', $state_id)->get();
        return $this->responseWithSuccess($data, 'Districts fetched successfully', 200);
    }
    public function getTehsils($district_id)
    {
        $data = Tehsil::select('id', 'name')->where('district_id', $district_id)->get();
        return $this->responseWithSuccess($data, 'Tehsils fetched successfully', 200);
    }

    public function getServicableVillages($tehsil_id)
    {
        try {
            $serviceableVillageIds = Area::withoutGlobalScopes()->whereIn(
                'id',
                ServiceArea::withoutGlobalScopes()->pluck('area_id')
            )->pluck('village_id')->unique()->toArray();

            // Get villages in the Tehsil
            $data = Village::select('id', 'name')
                ->where('tehsil_id', $tehsil_id)
                ->get()
                ->map(function ($village) use ($serviceableVillageIds) {
                    $village->is_serviceable = in_array($village->id, $serviceableVillageIds);
                    return $village;
                });

            return $this->responseWithSuccess($data, 'Villages fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
    public function getVillages($tehsil_id)
    {
        try {
            $data = Village::select('id', 'name')->where('tehsil_id', $tehsil_id)->get();
            return $this->responseWithSuccess($data, 'Villages fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
