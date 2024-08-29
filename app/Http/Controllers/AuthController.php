<?php

namespace App\Http\Controllers;

use App\DTOs\UserDto;
use App\DTOs\LoginDto;
use App\Events\SendEmail;
use App\Helpers\ResponseHelper;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            $userDto = UserDto::from($request->validated());
            $user = $this->authService->registerUser($userDto);
            return ResponseHelper::success($user, 'Registered and verification email sent. Please verify your email.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $loginDto = LoginDto::from($request->validated());
            $loginData = $this->authService->loginUser($loginDto->email, $loginDto->password);

            if (!$loginData) {
                return ResponseHelper::error('Invalid credentials', 401);
            }

            return ResponseHelper::success($loginData, 'Login successful.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            auth()->user()->tokens()->delete();
            return ResponseHelper::success(null, 'Logged out successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    // public function verifyEmail(Request $request, User $user): JsonResponse
    // {
    //     try {
    //         if (!$request->hasValidSignature()) {
    //             return ResponseHelper::error('The link has expired or is invalid.', 400);
    //         }

    //         if ($user->email_verified_at) {
    //             return ResponseHelper::error('Email is already verified.', 400);
    //         }

    //         $user->email_verified_at = now();
    //         $user->save();

    //         return ResponseHelper::success(null, 'Email verified successfully.');
    //     } catch (Exception $e) {
    //         return ResponseHelper::error($e->getMessage(), 500);
    //     }
    // }

    public function verifyEmail($userId)
    {
        try {
            $user = User::findOrFail($userId);

            // Retrieve frontend URL from environment variables
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/verify-email';

            if ($user->email_verified_at) {
                return Redirect::to("$frontendUrl?status=error&message=Email already verified");
            }

            // Mark the email as verified
            $user->email_verified_at = now();
            $user->save();

            return Redirect::to("$frontendUrl?status=success&message=Email verified successfully");
        } catch (Exception $e) {
            // Handle exception and redirect with an error message
            return Redirect::to("$frontendUrl?status=error&message=Verification failed");
        }
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        // Handle validation failure
        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->first(), 422);
        }

        try {
            $email = $request->email;
            $user = $this->authService->resendVerificationEmail($email);
            return ResponseHelper::success(null, 'Verification email resent successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }


}
