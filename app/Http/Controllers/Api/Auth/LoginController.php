<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use App\Models\User;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Services\User\UserService;
use App\Services\Auth\LoginService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Constants\User\UserConstants;
use App\Services\Auth\SanctumService;
use App\Exceptions\Auth\AuthException;
use App\Constants\General\ApiConstants;
use App\Http\Resources\User\UserResource;
use App\Constants\General\StatusConstants;
use App\Http\Resources\User\PreviewResource;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public $user_service;
    public $login_service;

    public function __construct()
    {
        $this->user_service = new UserService;
        $this->login_service = new LoginService;
    }

    public function login(Request $request)
    {
        try {
            $user = $this->login_service->authenticate($request->all());
            $data["user"] =  UserResource::make($user)->toArray($request);
            $data["token"] = $user->createToken(SanctumService::SESSION_KEY)->plainTextToken;
            LoginService::newLogin($user);
            return ApiHelper::validResponse("Logged in successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (AuthException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    public function oauthLogin(Request $request)
    {
        try {
            $data = $request->validate([
                'fcm_token' => 'nullable|string',
                "token" => "required|string",
                'provider' => 'required|in:google,apple,facebook,tiktok',
            ]);

            $payload = $this->login_service->ouath($data);

            $user = $payload["user"];
            $data = $payload["data"];

            $data["user"] =  UserResource::make($user)->toArray($request);
            $data["token"] = $user->createToken(SanctumService::SESSION_KEY)->plainTextToken;
            LoginService::newLogin($user);
            return ApiHelper::validResponse("Logged in successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (AuthException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, $request, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    // logout user
    public function logout()
    {
        try {
            auth()->user()->tokens()->delete();
            return ApiHelper::validResponse("User logged out successfully");
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function signInWithApple(Request $request)
    {
        $validatedData = $request->validate([
            'identity_token' => 'required|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
        ]);

        try {
            // 1. Get Apple Public Keys
            $applePublicKeys = Http::get('https://appleid.apple.com/auth/keys')->json();
            $jwkKeys = JWK::parseKeySet($applePublicKeys);

            // 2. Decode identity token using Apple public keys
            $decodedToken = JWT::decode($validatedData['identity_token'], $jwkKeys);

            // 3. Extract user identity
            $appleSub = $decodedToken->sub ?? null;
            $email = $decodedToken->email ?? $request->email;

            if (!$appleSub) {
                return response()->json(['error' => 'Invalid Apple token.'], 400);
            }

            // 4. Find existing user by Apple ID
            $user = User::where('apple_id', $appleSub)->first();

            // 5. If user does not exist, create one
            if (!$user) {
                // Optional: fallback match by email if same user used another method before
                $user = User::where('email', $email)->first();

                if ($user) {
                    // Link Apple ID to existing user
                    $user->update(['apple_id' => $appleSub]);
                } else {
                    $user = User::create([
                        'apple_id' => $appleSub,
                        'email' => $email,
                        'first_name' => $validatedData['first_name'] ?? '-',
                        'last_name' => $validatedData['last_name'] ?? '-',
                        'email_verified_at' => now(),
                        'password' => bcrypt('password'),
                        'status' => StatusConstants::ACTIVE,
                        'role' => UserConstants::USER,
                    ]);
                }
            }


            // 6. Create Sanctum token
            $token = $user->createToken('apple-signin')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Apple sign-in failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}