<?php

namespace App\Http\Controllers;

use App\Models\InterestedUser;
use App\Services\InterestedUsers\InterestedUsers;
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
            $users = InterestedUser::with(['user', 'state', 'tehsil', 'district', 'village'])->where('type', 'app')->get();

            $formatted = $users->map(function ($area) {
                return [
                    'id'           => $area->id,
                    'user_id'      => $area->user_id,
                    'name'         => $area->user->name,
                    'contact'      => $area->user->phone,
                    'village_id'   => $area->village_id,
                    'village_name' => $area->village?->name ?? null,
                    'tehsil_id'      => $area->tehsil_id,
                    'tehsil_name'    => $area->tehsil?->name ?? null,
                    'district_id'    => $area->district_id,
                    'district_name'  => $area->district?->name ?? null,
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
                'tehsil_id'    => 'required|exists:tehsils,id',
                'district_id' => 'required|exists:districts,id',
                'village_id' => 'required|exists:villages,id',
            ]);

            $user = $request->user();

            app(InterestedUsers::class)
        ->createInterestedUser($user, $request->only(['state_id','district_id','tehsil_id','village_id']));

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
            $interestedUser = InterestedUser::with(['user', 'state', 'district', 'tehsil', 'village'])->find($interestedUser->id);

            $formatted = [
                'id'           => $interestedUser->user_id,
                'village_id'   => $interestedUser->village_id,
                'village_name' => $interestedUser->village?->name ?? null,
                'tehsil_id'      => $interestedUser->tehsil_id,
                'tehsil_name'    => $interestedUser->tehsil?->name ?? null,
                'district_id'    => $interestedUser->district_id,
                'district_name'  => $interestedUser->district?->name ?? null,
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
                'tehsil_id'    => 'required|exists:tehsils,id',
                'district_id' => 'required|exists:districts,id',
                'village_id' => 'required|exists:villages,id',
            ]);

            $interestedUser = InterestedUser::find($id);
            $interestedUser->state_id = $request->state_id;
            $interestedUser->tehsil_id = $request->tehsil_id;
            $interestedUser->district_id = $request->district_id;
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
                    'contact'      => $area->phone,
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
