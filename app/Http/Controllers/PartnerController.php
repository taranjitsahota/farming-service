<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Riverline\MultiPartParser\Part;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $user = auth()->user();

            $query = User::role('partner')->with('partner', 'driver');

            if ($user->hasRole('admin')) {
                // admin → restrict to his substation
                $query->whereHas('partner.areas', function ($q) use ($user) {
                    $q->where('substation_id', $user->substation_id);
                });
            }

            $data = $query->get();
            
            $formattedData = $data->map(function ($item) {
                return [
                    'id' => $item->partner->id,
                    // 'user_id' => $item->id,
                    'name' => $item->name,
                    'email' => $item->email,
                    'phone' => $item->phone,
                    'company_name' => $item->partner->company_name,
                    'address' => $item->partner->address,
                    'is_driver' => $item->partner->is_driver,
                    'is_individual' => $item->partner->is_individual
                ];
            });
            return $this->responseWithSuccess($formattedData, 'Partner fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'phone' => 'required|unique:users,phone|regex:/^\+?[1-9]\d{1,14}$/',
            'company_name' => 'nullable|unique:partners,company_name',
            'is_driver' => 'required|boolean',
            'is_individual' => 'required|boolean',
            'license_number' => 'nullable|required_if:is_driver,1|unique:drivers',
            'experience_years' => 'nullable|required_if:is_driver,1|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
            ]);

            $user->assignRole('partner');

            $partner = Partner::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'address' => $request->address,
                'is_driver' => $request->is_driver ?? true,
                'is_individual' => $request->is_individual ?? true,
            ]);

            if ($request->boolean('is_driver')) {

                $driver = Driver::create([
                    'partner_id' => $partner->id,
                    'name'       => $request->name,
                    'phone'      => $request->phone,
                    'is_partner' => true,
                    'user_id'    => $user->id,
                    'license_number'   => $request->license_number,
                    'experience_years' => $request->experience_years,
                ]);

                $user->assignRole('driver');
            }

            DB::commit();

            return $this->responseWithSuccess([], 'Partner created successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseWithError($e->getMessage(), 500, 'Partner not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = User::find($id);
            return $this->responseWithSuccess($data, 'Partner fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'Partner not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $partner = Partner::findOrFail($id);

        $userId = $partner->user_id;

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $userId,
            'phone' => 'required|unique:users,phone,' . $userId,
            'company_name' => 'nullable|unique:partners,company_name,' . $id,
            'is_driver' => 'required|boolean',
            'is_individual' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {

            $user = $partner->user; // assuming Partner belongsTo User

            // Update user
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            // Update partner
            $partner->update([
                'company_name' => $request->company_name,
                'address' => $request->address,
                'is_driver' => $request->is_driver,
                'is_individual' => $request->is_individual,
            ]);

            // Handle driver status
            $driver = Driver::where('partner_id', $partner->id)->first();

            if ($request->boolean('is_driver')) {
                if (!$driver) {
                    // Create driver if not exists
                    Driver::create([
                        'partner_id' => $partner->id,
                        'name'       => $request->name,
                        'phone'      => $request->phone,
                        // 'is_partner' => true,
                        'user_id'    => $user->id,
                    ]);

                    $user->assignRole('driver');
                }
            } else {
                if ($driver) {
                    // Remove driver if already exists
                    $driver->delete();
                    $user->removeRole('driver');
                }
            }

            DB::commit();

            return $this->responseWithSuccess($partner, 'Partner updated successfully', 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseWithError($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $partner = Partner::with('user')->findOrFail($id);

            // delete user also when partner deleted
            if ($partner->user) {
                $partner->user->delete();
            }

            $partner->delete();

            return $this->responseWithSuccess(null, 'Partner and associated User deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500);
        }
    }

    public function AssignPartner(Request $request)
    {
        try {
            $request->validate([
                'partner_id' => 'required',
                'booking_id' => 'required'
            ]);

            $booking = Booking::find($request->booking_id);
            $booking->partner_id = $request->partner_id;
            $booking->admin_note = $request->admin_note;
            $booking->save();

            return $this->responseWithSuccess($booking, 'Partner assigned successfully', 200);
        } catch (ValidationException $e) {
            return $this->responseWithError($e->errors(), 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500);
        }
    }
}
