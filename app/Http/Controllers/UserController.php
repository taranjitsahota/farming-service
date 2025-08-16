<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = User::where('role', 'user')->get();
            return $this->responseWithSuccess($user, 'user fetched successfully', 200);
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
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|digits:6|confirmed',
                'phone' => 'required|unique:users,phone|regex:/^\+?[1-9]\d{1,14}$/',
            ]);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => $request->role,
                'is_verified' => true
            ]);

            return $this->responseWithSuccess($user, 'User registered successfully', 201);
        } catch(\Illuminate\Validation\ValidationException $e){
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422, $e->validator->errors());
        }catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::find($id);
            return $this->responseWithSuccess($user, 'user fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|unique:users,phone|regex:/^\+?[1-9]\d{1,14}$/',
        ]);

        try {
            $user = User::find($id);
            $user->update($request->only(['name', 'email', 'phone', 'substation_id']));


            return $this->responseWithSuccess($user, 'User updated successfully', 201);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::find($id);
            $user->delete();
            return $this->responseWithSuccess($user, 'User deleted successfully', 201);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    public function adminList()
    {
        try {
            $user = User::where('role', 'admin')->with('substation')->get();
            $formatter = $user->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'substation' => $user->substation ? $user->substation->name : null,
                    'substation_id' => $user->substation ? $user->substation->id : null
                ];
            });
            return $this->responseWithSuccess($formatter, 'user fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    public function driverList()
    {
        try{
            $user = User::where('role', 'driver')->with('substation')->get();
            $formatter = $user->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'substation' => $user->substation ? $user->substation->name : null,
                    'substation_id' => $user->substation ? $user->substation->id : null
                ];
            });
            return $this->responseWithSuccess($formatter, 'Driver fetched successfully', 200);
        }catch(\Exception $e){
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    public function uploadProfilePic(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            ]);

            $path = $request->file('file')->store("users/{$request->user()->id}", 's3');
            /** @var \Illuminate\Contracts\Filesystem\Cloud $disk */
            $disk = Storage::disk('s3');
            $url = $disk->url($path);
            $user = $request->user();
            $user->profile_photo_url = $url;
            $user->profile_photo_path = $path;
            $user->save();

            return $this->responseWithSuccess($url, 'Profile picture uploaded successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    public function deleteUploadProfilePic($id)
    {
        try {
            $user = User::find($id);

            if (!$user || !$user->profile_photo_path) {
                return $this->responseWithError('Profile photo not found', 404);
            }

            $path = $user->profile_photo_path;

            if (Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }

            $user->profile_photo_path = null;
            $user->profile_photo_url = null;
            $user->save();

            return $this->responseWithSuccess($user, 'Profile picture deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
