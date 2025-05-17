<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use App\Models\State;
use App\Models\City;
use App\Models\ServiceArea;
use App\Models\Village;

class LocationController extends Controller
{


    public function getStates()
    {
        $data = State::select('id', 'name')->get();
        return $this->responseWithSuccess($data, 'States fetched successfully', 200);
    }

    public function getCities($state_id)
    {
        $data = City::select('id', 'name')->where('state_id', $state_id)->get();
        return $this->responseWithSuccess($data, 'Cities fetched successfully', 200);
    }

    public function getServicableVillages($city_id)
    {
        try{
        $serviceableVillageIds = Area::whereIn(
            'id',
            ServiceArea::pluck('area_id')
        )->pluck('village_id')->unique()->toArray();

        // Get villages in the city
        $data = Village::select('id', 'name')
            ->where('city_id', $city_id)
            ->get()
            ->map(function ($village) use ($serviceableVillageIds) {
                $village->is_serviceable = in_array($village->id, $serviceableVillageIds);
                return $village;
            });

        return $this->responseWithSuccess($data, 'Villages fetched successfully', 200);

        }catch(\Exception $e){
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
    public function getVillages($city_id){
        try{
        $data = Village::select('id', 'name')->where('city_id', $city_id)->get();
        return $this->responseWithSuccess($data, 'Villages fetched successfully', 200);
        }catch(\Exception $e){
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
