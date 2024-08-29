<?php

namespace App\Http\Controllers;

use App\DTOs\UserDto;
use App\DTOs\LoginDto;
use App\Events\SendEmail;
use App\Helpers\ResponseHelper;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdatePasswordByEmailRequest;
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

    public function forgotPassword(Request $request): JsonResponse
    {
        // Validate the email input
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $validated['email'])->first();

            // Generate a reset token
            $token = Str::random(60);

            // Save the token in the database
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $validated['email']],
                ['token' => $token, 'created_at' => Carbon::now(),'is_valid' => true],

            );

            // Prepare email data
            $resetUrl = env('FRONTEND_URL') . "/reset-password-by-email?token=$token&email={$validated['email']}";
            $emailData = [
                'name' => $user->name,
                'reset_url' => $resetUrl,
            ];

            // Trigger the email event
            event(new SendEmail('password_reset', $emailData, $validated['email']));

            return ResponseHelper::success(null, 'Password reset link sent successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to send password reset email: ' . $e->getMessage(), 500);
        }
    }

        public function resetPasswordByEmail(UpdatePasswordByEmailRequest $request)
    {
        $validated = $request->validated();

        try {
            // Find the token and email in the password reset tokens table
            $resetToken = \DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->where('token', $validated['token'])
                ->where('is_valid', true) // Check if the token is still valid
                ->first();

            if (!$resetToken) {
                return ResponseHelper::error('Invalid token, email, or the token has already been used.', 400);
            }

            // Check if the token is expired (assuming a token expiration time, e.g., 60 minutes)
            if (\Carbon\Carbon::parse($resetToken->created_at)->addMinutes(60)->isPast()) {
                return ResponseHelper::error('Token has expired.', 400);
            }

            // Get the user by email
            $user = User::where('email', $validated['email'])->first();
            if (!$user) {
                return ResponseHelper::error('User not found.', 404);
            }

            // Update the user's password
            $user->password = bcrypt($validated['password']);
            $user->save();

            // Mark the token as invalid instead of deleting it
            \DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->where('token', $validated['token'])
                ->update(['is_valid' => false]);

            return ResponseHelper::success(null, 'Password reset successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to reset password: ' . $e->getMessage(), 500);
        }
    }


}
