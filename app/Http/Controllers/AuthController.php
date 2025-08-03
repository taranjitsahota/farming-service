<?php

namespace App\Http\Controllers;

use App\Jobs\SendOtpJob;
use App\Models\OtpVerification;
use App\Models\User;
use App\Models\UserInfo;
use App\Services\Otp\GenerateOtp;
use App\Services\Otp\SendOtp;
use App\Services\Otp\VerifyOtp;
use App\Traits\Auth\AuthUser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{

    //----------------------------------------- Website Functions -------------------------------------------------------

    use AuthUser;

    /**
     * Register a new superadmin or admin.
     *
     * @OA\Post(
     *     path="/api/register-superadmin-admin",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *     summary="Register a new superadmin or admin",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "email", "password", "password_confirmation", "phone", "role"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="role", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(response=201, ref="#/components/responses/201"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */


    public function registerSuperadminAdmin(Request $request)
    {

        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|string|confirmed|min:8|max:25',
                'phone' => 'required|max:15',
                'role' => 'required|string|max:12',
                'substation_id' => 'required_if:role,admin|exists:substations,id',
            ]);

            $user = $this->register($request, $request->role);

            if ($user) {

                return $this->responseWithSuccess([$user], 'User registered successfully', 201);
            } else {

                return $this->responseWithError('User registration failed!', 500);
            }
        } catch (\Illuminate\Validation\ValidationException $th) {

            return $this->responseWithError('Validation failed', 422, $th->validator->errors());
        } catch (\Exception $e) {

            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }




    /**
     * Log in admin-superadmin.
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

            $user = $this->processLoginAdminSuperadmin($request);

            if (isset($user)) {

                $browserHash = hash('sha256', $request->header('User-Agent') . $request->ip());
                $otpRequired = $this->isOtpRequired($user, $browserHash);

                if ($otpRequired) {

                    $otp = GenerateOtp::GenereateOtp();
                    $sendEmail = SendOtp::SendOtpMail($user->email, $otp);

                    if ($sendEmail) {

                        $this->storeOtpVerification($user->id, $otp);

                        $data = [
                            'user_id' => $user->id,
                            'otp' => true,
                            'email' => $user->email,
                        ];

                        return $this->responseWithSuccess($data, 'OTP has been sent to your email. Please verify it.', 200);
                    } else {

                        return $this->responseWithError('Something went wrong!', 500);
                    }
                }


                /** @var \App\Models\User $user */
                $token = $user->createToken('AdminToken')->plainTextToken;
                $trimmedToken = explode('|', $token)[1];

                $data = [
                    'token' => $trimmedToken,
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_completed' => $user->profile_completed,
                    'profile_photo_url' => $user->profile_photo_url
                ];

                return $this->responseWithSuccess($data, 'Logged in successfully', 200);
            }

            return $this->responseWithError('Invalid credentials', 401, []);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return $this->responseWithError('Validation failed', 422, $th->validator->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    //----------------------------------------- App Functions -------------------------------------------------------
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     description="Registers a new user with a name, contact number, country code, and optional email.",
     *     operationId="registerUser",
     *     tags={"Authentication"},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe", description="User's full name"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com", description="User's email (optional, must be unique)"),
     *             @OA\Property(property="phone", type="string", example="9876543210", description="User's contact number (must be unique)"),
     *             @OA\Property(property="password", type="string", example="1234", description="4-6 digit password for authentication"),
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
                'email' => 'nullable|email|unique:users,email|max:255',
                'phone' => 'required|unique:users,phone|regex:/^\+?[1-9]\d{1,14}$/',
                'password' => 'required|min:4|max:6',
            ]);

            $user = $this->register($request, null);

            if ($user) {
                return $this->responseWithSuccess([], 'User registered successfully', 201);
            } else {
                return $this->responseWithError('User registration failed!', 500);
            }
        } catch (\Illuminate\Validation\ValidationException $th) {
            return $this->responseWithError('Validation failed', 422, $th->validator->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
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
                // 'first_name' => 'required|string|max:255',
                // 'last_name' => 'nullable|string|max:255',
                'fathers_name' => 'required|string|max:255',
                'pincode' => 'required|max:25',
                'village' => 'required|string|max:255',
                'post_office' => 'required|string|max:255',
                'police_station' => 'required|string|max:255',
                'district' => 'required|string|max:255',
                'total_servicable_land' => 'required|string|max:255',
            ]);



            $this->processcompleteUserProfile($request);

            return $this->responseWithSuccess([], 'Profile completed successfully!', 200);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return $this->responseWithError('Validation failed', 422, $th->validator->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login-user",
     *     summary="User login",
     *     description="Logs in a user using contact number and password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"phone", "password"},
     *             @OA\Property(property="phone", type="string", example="9876543210"),
     *             @OA\Property(property="password", type="string", example="1234")
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
                'phone' => 'required|exists:users,phone',
                'password' => 'required',
            ]);

            $result = $this->processUser($request);

            if (!$result) {
                return $this->responseWithError('Invalid PIN or contact number', 401);
            }

            [$user, $trimmedToken] = $result;

            $data = [
                'id' => $user->id,
                'token' => $trimmedToken,
                'username' => $user->name,
                'role' => $user->role,
                'profile_completed' => $user->profile_completed
            ];
            return $this->responseWithSuccess($data, 'Login successful', 200);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return $this->responseWithError('Validation failed', 422, $th->validator->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Invalid Pin', 500, $e->getMessage());
        }
    }

    public function sendOtpUser(Request $request)
    {

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|unique:users,phone|regex:/^\d{5,15}$/',
                'email' => 'nullable|email',
                'pin' => 'required|min:6|confirmed',
            ], [
                'phone.unique' => 'You are already registered.',
            ]);

            // Create temporary unverified user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->pin),
                'is_verified' => false,
            ]);

            $otp = GenerateOtp::GenereateOtp();

            $number = $request->phone;

            $sendMsg = SendOtp::sendOtpPhone($number, $otp);

            if (!$sendMsg) {
                return $this->responseWithError('Something went wrong!', 500, 'Failed to send OTP');
            }

            $this->storeOtpVerification($user->id, $otp);

            $data = [
                'user_id' => $user->id
            ];

            return $this->responseWithSuccess($data, 'OTP sent to your phone successfully.', 200);
        } catch (\Illuminate\Validation\ValidationException $th) {

            $firstError = collect($th->validator->errors()->all())->first();

            return $this->responseWithError($firstError, 422, $th->validator->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    public function verifyOtpUser(Request $request)
    {
        // add more fields to validate the things
        $request->validate([
            'otp' => 'required',
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $user = User::where('id', $request->user_id)
                ->where('is_verified', false)
                ->first();

            if (!$user) {
                return $this->responseWithError('No unverified user found or already verified.', 422);
            }

            $otpRecord = Otpverification::where('user_id', $user->id)->first();

            if (!$otpRecord || $otpRecord->otp !== $request->otp) {
                return $this->responseWithError('Invalid OTP.', 422);
            }

            if ($otpRecord->expires_at < now()) {
                return $this->responseWithError('OTP expired.', 422);
            }

            // Mark as verified
            $user->update(['is_verified' => true]);
            $otpRecord->delete();

            return $this->responseWithSuccess([], 'User registered successfully.', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }



    //----------------------------------------- Common Functions -------------------------------------------------------

    /**
     * @OA\Post(
     *     path="/api/auth/verify-otp",
     *     summary="Verify OTP for authentication or password reset",
     *     description="Users submit the OTP received via email or phone to verify their identity. The type parameter determines the purpose of OTP verification.",
     *     operationId="verifyOtp",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"user_id", "otp", "type"},
     *                 @OA\Property(
     *                     property="user_id", 
     *                     type="integer", 
     *                     example=1, 
     *                     description="ID of the user"
     *                 ),
     *                 @OA\Property(
     *                     property="otp", 
     *                     type="integer", 
     *                     example=123456, 
     *                     description="The OTP code received via email or phone"
     *                 ),
     *                 @OA\Property(
     *                     property="type", 
     *                     type="string", 
     *                     enum={"first_time", "forgot_password", "change_password"}, 
     *                     example="first_time", 
     *                     description="Purpose of OTP verification"
     *                 )
     *             )
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
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'otp' => 'required|integer',
                'type' => 'required|in:login,forgot_password',
            ]);

            $otpVerified = VerifyOtp::verifyOtp($request->user_id, $request->otp);

            if (!$otpVerified) {
                return $this->responseWithError('Invalid or expired OTP', 400, []);
            }

            $browserHash = hash('sha256', $request->header('User-Agent') . $request->ip());

            OtpVerification::where('user_id', $request->user_id)
                ->latest()
                ->first()
                ->update([
                    'browser_hash' => $browserHash,
                    'verified_at' => now(),
                ]);

            $user = User::find($request->user_id);
            if (!$user) {
                return $this->responseWithError('User not found', 422, []);
            }

            if ($request->type === 'login') {
                $user->email_verified_at = now();
                $user->save();

                $token = $user->createToken('LaravelApp')->plainTextToken;
                $trimmedToken = explode('|', $token)[1];

                $data = [
                    'id' => $user->id,
                    'token' => $trimmedToken,
                    'name' => $user->name,
                    'role' => $user->role,
                    'profile_photo_url' => $user->profile_photo_url,
                    'profile_completed' => $user->profile_completed
                ];

                return $this->responseWithSuccess($data, 'OTP verified successfully. Login successfull.', 200);
            }

            return $this->responseWithSuccess([], 'OTP verified successfully. Proceed to reset password.', 200);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return $this->responseWithError('Validation failed', 422, $th->validator->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }


    /**
     * @OA\Post(
     *     path="/api/resend-otp",
     *     summary="Resend OTP to user",
     *     description="Resends an OTP to the user's registered email or phone number based on the selected contact type.",
     *     operationId="resendOtp",
     *     tags={"Authentication"},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "contact_type"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="The ID of the user requesting a new OTP"),
     *             @OA\Property(property="contact_type", type="string", enum={"email", "phone"}, example="email", description="The method by which the user wants to receive the OTP")
     *         )
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="400", ref="#/components/responses/400"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */


    public function resendOtp(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'contact_type' => 'required|in:email,phone,login',
            ]);

            // Find user
            $user = User::find($request->user_id);

            if (!$user) {
                return $this->responseWithError('User not found', 422, []);
            }

            $otp = GenerateOtp::GenereateOtp();
            $this->storeOtpVerification($user->id, $otp);

            if ($request->contact_type === 'phone') {
                // Check if the user has a valid phone number
                if (!$user->phone) {
                    return $this->responseWithError('Phone number not found for this user', 400, []);
                }

                $messageSid = SendOtp::sendOtpPhone($user->full_phone_number, $otp);

                $data = [
                    'sid' => $messageSid
                ];

                return $this->responseWithSuccess($data, 'New OTP sent to your phone.', 200);
            } else {
                // Check if the user has a valid email
                if (!$user->email) {
                    return $this->responseWithError('Email not found for this user.', 400, []);
                }

                SendOtp::SendOtpMail($user->email, $otp);

                return $this->responseWithSuccess([], 'New OTP sent to your email.', 200);
            }
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }





    /**
     * @OA\Post(
     *     path="/api/send-otp-for-password-reset",
     *     summary="Send OTP for password reset",
     *     description="Send OTP via email or phone for password reset. The user must exist in the database.",
     *     operationId="sendOtpForPasswordReset",
     *     tags={"Authentication"},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"contact_type", "contact"},
     *             @OA\Property(property="contact_type", type="string", enum={"email", "phone"}, example="phone", description="Specify whether OTP should be sent to email or phone"),
     *             @OA\Property(property="contact", type="string", example="9876543210", description="User's email or phone number")
     *         )
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response="401", ref="#/components/responses/401"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="429", description="Too many OTP requests, try again later"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */


    public function sendOtpForPasswordReset(Request $request)
    {
        try {
            $request->validate([
                'contact_type' => 'required|in:email,phone',
                'contact' => 'required|string',
            ]);

            $contactType = $request->contact_type;
            $contact = $request->contact;

            $key = "otp_attempts:$contact";

            if (RateLimiter::tooManyAttempts($key, 5)) {
                return $this->responseWithError('Too many attempts. Try later.', 429);
            }

            RateLimiter::hit($key, 60);

            $user = $contactType === 'phone' ?
                User::where('phone', $contact)->first() :
                User::where('email', $contact)->first();

            if (!$user) {
                return $this->responseWithError('User not registered with us.', 401, []);
            }

            $otp = GenerateOtp::GenereateOtp();
            $this->storeOtpVerification($user->id, $otp);

            if ($contactType === 'phone') {
                dispatch(new SendOtpJob($user->full_phone_number, $otp, $contactType));
                return $this->responseWithSuccess(['user_id' => $user->id], 'OTP sent successfully via phone', 200);
            } else {
                dispatch(new SendOtpJob($user->email, $otp, $contactType));
                return $this->responseWithSuccess(['user_id' => $user->id], 'OTP sent successfully via email', 200);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->validator->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/change-password",
     *     summary="Change password using old credentials or reset via OTP",
     *     description="Users can change their password using the old password or reset it via OTP.",
     *     operationId="changePassword",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"user_id", "type", "new_password"},
     *                 @OA\Property(property="user_id", type="integer", example=1, description="ID of the user"),
     *                 @OA\Property(
     *                     property="type", 
     *                     type="string", 
     *                     enum={"first_time", "forgot_password", "change_password"},
     *                     example="change_password",
     *                     description="Type of password change request: 'first_time' for initial setup, 'forgot_password' for resetting via OTP, 'change_password' for changing with old password"
     *                 ),
     *                 @OA\Property(
     *                     property="old_password", 
     *                     type="string", 
     *                     example="OldPassword123",
     *                     description="Old password (Required only if type is 'change_password')"
     *                 ),
     *                 @OA\Property(
     *                     property="new_password", 
     *                     type="string", 
     *                     example="NewSecurePass123",
     *                     description="New password for the user"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */

    public function changePassword(Request $request)
    {
        try {
            $type = $request->input('type');

            $rules = [
                'user_id' => 'required|exists:users,id',
                'type' => 'required|in:change_password,forgot_password',
                'new_password' => [
                    'required',
                    'string',
                    'confirmed',
                    $type === 'change_password' ? 'min:8' : 'min:6',
                ],
            ];

            if ($type === 'change_password') {
                $rules['old_password'] = 'required|string';
            }

            $request->validate($rules);
            // $request->validate([
            //     'user_id' => 'required|exists:users,id',
            //     'new_password' => 'required|string|confirmed|min:6',
            //     'type' => 'required|in:first_time,forgot_password,change_password',
            //     'old_password' => Rule::requiredIf($request->type === 'change_password'), // Require old password only for change_password
            // ]);

            $user = User::find($request->user_id);

            if (!$user) {
                return $this->responseWithError('User not found', 422, []);
            }

            // Ensure old password is verified only for password change, not first-time setup or forgot password
            if ($request->type === 'change_password') {
                if (!Hash::check($request->old_password, $user->password)) {
                    return $this->responseWithError('Old password is incorrect.', 400, []);
                }
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            // Logout all sessions after password change
            $user->tokens()->delete();

            return $this->responseWithSuccess([], 'Password updated successfully. Proceed to Login', 200);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return $this->responseWithError('Validation failed', 422, $th->validator->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout a user",
     *     description="Logs out the currently authenticated user and deletes their tokens",
     *     tags={"Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=500, ref="#/components/responses/500"),
     * )
     */

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->responseWithError('Unauthorized', 401);
            }

            // Revoke all tokens for the user
            $user->tokens()->delete();

            return $this->responseWithSuccess([], 'Logged out successfully.', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
