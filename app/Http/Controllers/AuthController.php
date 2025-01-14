<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Auth\AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    use AuthUser;

        public function register(Request $request)
    {

        try {

            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|confirmed',
            ]);

            $this->processRegistration($request);

            return response()->json([
                'message' => 'User registered successfully!',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return response()->json(['errors' => $th->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string',
            ]);
            $result = $this->processLogin($request);
            if (isset($result['token'])) {
                return response()->json($result);
            }
            return response()->json(['message' => 'Invalid credentials'], 401);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return response()->json(['errors' => $th->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }


}
