<?php

namespace App\Http\Controllers;

use App\Models\InterestedUser;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;

class InterestedUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = InterestedUser::with(['user', 'state', 'city', 'village'])->where('type', 'app')->get();

            $formatted = $users->map(function ($area) {
                return [
                    'id'           => $area->id,
                    'user_id'      => $area->user_id,
                    'name'         => $area->user->name,
                    'contact'      => $area->user->contact,
                    'village_id'   => $area->village_id,
                    'village_name' => $area->village?->name ?? null,
                    'city_id'      => $area->city_id,
                    'city_name'    => $area->city?->name ?? null,
                    'state_id'     => $area->state_id,
                    'state_name'   => $area->state?->name ?? null,
                    'requested_date'   => $area->created_at->format('Y-m-d')
                ];
            });

            return $this->responseWithSuccess($formatted, 'Interested users retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $request->validate([
                'state_id'   => 'required|exists:states,id',
                'city_id'    => 'required|exists:cities,id',
                'village_id' => 'required|exists:villages,id',
            ]);

            $user = $request->user();

            InterestedUser::create([
                'user_id' => $user->id,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'village_id' => $request->village_id,
                'type' => 'app'
            ]);

            return $this->responseWithSuccess([], 'Interested user created successfully', 201);
        } catch (ValidationException $e) {
            return $this->responseWithError($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(InterestedUser $interestedUser)
    {
        try {
            $interestedUser = InterestedUser::with(['user', 'state', 'city', 'village'])->find($interestedUser->id);

            $formatted = [
                'id'           => $interestedUser->user_id,
                'village_id'   => $interestedUser->village_id,
                'village_name' => $interestedUser->village?->name ?? null,
                'city_id'      => $interestedUser->city_id,
                'city_name'    => $interestedUser->city?->name ?? null,
                'state_id'     => $interestedUser->state_id,
                'state_name'   => $interestedUser->state?->name ?? null,
            ];

            return $this->responseWithSuccess($formatted, 'Interested user retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'state_id'   => 'required|exists:states,id',
                'city_id'    => 'required|exists:cities,id',
                'village_id' => 'required|exists:villages,id',
            ]);

            $interestedUser = InterestedUser::find($id);
            $interestedUser->state_id = $request->state_id;
            $interestedUser->city_id = $request->city_id;
            $interestedUser->village_id = $request->village_id;
            $interestedUser->save();

            return $this->responseWithSuccess([], 'Interested user updated successfully', 200);
        } catch (ValidationException $e) {
            return $this->responseWithError($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $interestedUser = InterestedUser::find($id);
            $interestedUser->delete();
            return $this->responseWithSuccess([], 'Interested user deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
    public function interestedUsersEmail(){
        try{
            $interestedUsers = InterestedUser::where('type', 'email')->get();

            $formatted = $interestedUsers->map(function ($area) {
                return [
                    'id'           => $area->id,
                    'name'         => $area->name,
                    'contact'      => $area->contact_number,
                    'email'        => $area->email,
                    'village_name' => $area->village_name,
                    'pincode'      => $area->pincode,
                    'district'     => $area->district,
                    'area_of_land' => $area->area_of_land,
                    'land_unit'    => $area->land_unit,
                    'requested_date'   => $area->created_at->format('Y-m-d')
                ];
            });

            return $this->responseWithSuccess($formatted, 'Interested users retrieved successfully', 200);
        }catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
