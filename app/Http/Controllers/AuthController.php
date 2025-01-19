<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInfo;
use App\Services\GenerateOtp\GenerateOtpMail;
use App\Traits\Auth\AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    use AuthUser;

    /**
     * Register a new user.
     *
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "email", "password", "password_confirmation", "role_id"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="role", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully!",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong!"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     */

        public function registeradmin(Request $request)
    {

        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|string|confirmed|max:100|min:8',
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

    public function registerUser(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'contact_number' => 'required|unique:users,contact_number',
            'pin' => 'required|min:4',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'fathers_name' => 'required|string|max:255',
            'pincode' => 'required|max:255',
            'village' => 'required|string|max:255',
            'post_office' => 'required|string|max:255',
            'police_station' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'total_servicable_land' => 'required|string|max:255',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the user in the users table
        $user = User::create([
            'email' => $request->email,
            'name' => $request->first_name,
            'contact_number' => $request->contact_number,
            'pin' => Hash::make($request->pin), // Hash the pin
        ]);

        // Create associated user info in the userinfos table
        $userInfo = UserInfo::create([
            'user_id' => $user->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'fathers_name' => $request->fathers_name,
            'pincode' => $request->pincode,
            'village' => $request->village,
            'post_office' => $request->post_office,
            'police_station' => $request->police_station,
            'district' => $request->district,
            'total_servicable_land' => $request->total_servicable_land,
            'fathers_name' => $request->fathers_name,
        ]);

        // Return a success message with the user info
        return response()->json([
            'message' => 'User registered successfully',
        ], 201);
    }

    public function loginuser(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'contact_number' => 'required|exists:users,contact_number',
            'pin' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the user by contact number
        $user = User::where('contact_number', $request->contact_number)->first();

        // Check if the PIN matches
        if (Hash::check($request->pin, $user->pin)) {
            // Generate an API token for the user
            $token = $user->createToken('YourAppName')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token
            ], 200);
        } else {
            return response()->json(['error' => 'Invalid PIN'], 401);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|integer',
        ]);
    
        $user = User::find($request->user_id);

        if ($user && $user->otp === $request->otp && $user->otp_expiry >= now()) {
            // Clear OTP after verification
            $user->otp = null;
            $user->otp_expiry = null;
            $user->save();
    
            $token = $user->createToken('LaravelApp')->plainTextToken;
            $trimmedToken = explode('|', $token)[1];
    
            return response()->json([
                'message' => 'Login successful',
                'token' => $trimmedToken,
                'user' => $user,
            ]);
        }
    
        return response()->json(['message' => 'Invalid or expired OTP'], 400);
    }
    

        public function resendOtp(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);

            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            // Generate new OTP
            GenerateOtpMail::GenereateOtp($user);

            return response()->json([
                'message' => 'New OTP sent to your email.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



     /**
     * Log in a user.
     *
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Log in a user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="Bearer eyJhbGciOiJIUzI1NiIsInR...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong!"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     */

     
     public function login(Request $request)
     {
         try {
             $request->validate([
                 'email' => 'required|email|exists:users,email',
                 'password' => 'required|string',
             ]);
     
             $result = $this->processLogin($request);

             if (isset($result['user'])) {

                 $otp = GenerateOtpMail::GenereateOtp($result['user']);
     
                 return response()->json([
                     'message' => 'OTP verification required',
                     'otp_sent' => true,
                     'user_id' => $result['user']->id,
                 ]);
                 
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
