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
     * Register a new admin & superadmin.
     *
     * @OA\Post(
     *     path="/api/register-superadmin-admin",
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
     *     @OA\Response(response=201, ref="#/components/responses/201"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */

        public function registerSuperadminAdmin(Request $request)
    {

        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|string|confirmed|max:100|min:8',
                'role' => 'required|string',
            ]);

            $this->RegistrationSuperadminAdmin($request);

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

    /**
 * Register a new user.
 *
 * @OA\Post(
 *     path="/api/register-user",
 *     tags={"Authentication"},
 *     summary="Register a new admin & superadmin",
 *     description="Registers a new user with name, email (optional), contact number, and pin.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"name", "contact_number", "pin"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", nullable=true, example="john.doe@example.com"),
 *             @OA\Property(property="contact_number", type="string", example="+1234567890"),
 *             @OA\Property(property="pin", type="string", example="1234"),
 *         )
 *     ),
 *     @OA\Response(response=201, ref="#/components/responses/201"),
 *     @OA\Response(response=422, ref="#/components/responses/422"),
 *     @OA\Response(response=500, ref="#/components/responses/500")
 * )
 */
    
        public function registerUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email',
                'contact_number' => 'required|unique:users,contact_number',
                'pin' => 'required|min:4',
            ]);

            $user = $this->processRegistrationUser($request);

            if ($user) {
                return response()->json([
                    'message' => 'User registered successfully!',
                ], 201);
            } else {
                return response()->json([
                    'message' => 'User registration failed!',
                ], 500); 
            }
        } catch (\Illuminate\Validation\ValidationException $th) {
            return response()->json(['errors' => $th->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
 * @OA\Post(
 *     path="/api/complete-profile",
 *     summary="Complete user profile",
 *     description="Allows authenticated users to complete their profile by providing additional details.",
 *     tags={"User Profile completion"},
 *     security={{"sanctum":{}}}, 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"first_name", "fathers_name", "pincode", "village", "post_office", "police_station", "district", "total_servicable_land"},
 *             @OA\Property(property="first_name", type="string", example="John"),
 *             @OA\Property(property="last_name", type="string", example="Doe"),
 *             @OA\Property(property="fathers_name", type="string", example="Robert Doe"),
 *             @OA\Property(property="pincode", type="string", example="123456"),
 *             @OA\Property(property="village", type="string", example="Greenfield"),
 *             @OA\Property(property="post_office", type="string", example="Greenfield PO"),
 *             @OA\Property(property="police_station", type="string", example="Greenfield PS"),
 *             @OA\Property(property="district", type="string", example="Central District"),
 *             @OA\Property(property="total_servicable_land", type="string", example="5 acres")
 *         )
 *     ),
 *     @OA\Response(response=200, ref="#/components/responses/200"),
 *     @OA\Response(response=401, ref="#/components/responses/401"),
 *     @OA\Response(response=422, ref="#/components/responses/422"),
 *     @OA\Response(response=500, ref="#/components/responses/500")
 * )
 */

    public function completeUserProfile(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'fathers_name' => 'required|string|max:255',
                'pincode' => 'required|string|max:255',
                'village' => 'required|string|max:255',
                'post_office' => 'required|string|max:255',
                'police_station' => 'required|string|max:255',
                'district' => 'required|string|max:255',
                'total_servicable_land' => 'required|string|max:255',
            ]);

           

            $this->processcompleteUserProfile($request);
          

            return response()->json([
                'message' => 'Profile completed successfully!',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return response()->json(['errors' => $th->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
 * @OA\Post(
 *     path="/api/login-user",
 *     summary="User login",
 *     description="Logs in a user using contact number and PIN",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"contact_number", "pin"},
 *             @OA\Property(property="contact_number", type="string", example="9876543210"),
 *             @OA\Property(property="pin", type="string", example="1234")
 *         )
 *     ),
 *     @OA\Response(response=200, ref="#/components/responses/200"),
 *     @OA\Response(response=401, ref="#/components/responses/401"),
 *     @OA\Response(response=422, ref="#/components/responses/422"),
 *     @OA\Response(response=500, ref="#/components/responses/500")
 * )
 */

    public function loginUser(Request $request)
    {

        try {

        $request->validate([
            'contact_number' => 'required|exists:users,contact_number',
            'pin' => 'required',
        ]);

        $this->processRegistrationUser($request);

        $user = User::where('contact_number', $request->contact_number)->first();

        if (Hash::check($request->pin, $user->pin)) {

            $token = $user->createToken('YourAppName')->plainTextToken;
            $trimmedToken = explode('|', $token)[1];

            return response()->json([
                'message' => 'Login successful',
                'token' => $trimmedToken,
                'profile_completed' => $user->profile_completed,
                'role' => $user->role,
            ], 200);
        } else {
            return response()->json(['error' => 'Invalid PIN'], 401);
        }
    } catch (\Illuminate\Validation\ValidationException $th) {
        return response()->json(['errors' => $th->validator->errors()], 422);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Invalid PIN',
            'error' => $e->getMessage(),
        ], 500);
    }
    }

    /**
 * @OA\Post(
 *     path="/api/verify-otp",
 *     summary="Verify OTP",
 *     description="Verifies the OTP and logs in the user",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"user_id", "otp"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="otp", type="integer", example=123456)
 *         )
 *     ),
 *     @OA\Response(response=200, ref="#/components/responses/200"),
 *     @OA\Response(response=400, description="Invalid or expired OTP", 
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Invalid or expired OTP")
 *         )
 *     ),
 *     @OA\Response(response=422, ref="#/components/responses/422"),
 *     @OA\Response(response=500, ref="#/components/responses/500")
 * )
 */

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
    
    /**
 * @OA\Post(
 *     path="/api/resend-otp",
 *     summary="Resend OTP",
 *     description="Resends OTP to the user's registered email",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"user_id"},
 *             @OA\Property(property="user_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(response=200, description="OTP sent successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="New OTP sent to your email.")
 *         )
 *     ),
 *     @OA\Response(response=404, description="User not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User not found.")
 *         )
 *     ),
 *     @OA\Response(response=500, ref="#/components/responses/500")
 * )
 */

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
     *     path="/api/login-superadmin-admin",
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
         *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response="401", ref="#/components/responses/401"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */

     
     public function loginSuperadminAdmin(Request $request)
     {
         try {
             $request->validate([
                 'email' => 'required|email|exists:users,email',
                 'password' => 'required|string',
             ]);
     
             $result = $this->processLoginAdminSuperadmin($request);

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

     /**
 * @OA\Post(
 *     path="/api/logout",
 *     summary="Logout a user",
 *     description="Logs out the currently authenticated user and deletes their tokens",
 *     tags={"Authentication"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, 
 *         description="Successfully logged out",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Logged out successfully.")
 *         )
 *     ),
 *     @OA\Response(response=401, 
 *         description="Unauthorized access",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthorized")
 *         )
 *     ),
 *     @OA\Response(response=500, ref="#/components/responses/500")
 * )
 */

        public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function getPincodeForVillage($villageName)
{
    // dd($villageName);
    $url = "https://api.postalpincode.in/postoffice/" . urlencode($villageName);
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!empty($data[0]['PostOffice'])) {
        return $data[0]['PostOffice'][0]['Pincode'];
    }

    return "Pincode not found";
}


}
