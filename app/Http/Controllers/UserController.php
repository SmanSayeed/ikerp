<?php

namespace App\Http\Controllers;

use App\DTOs\UpdateUserDto;
use App\DTOs\UserDTO;
use App\Helpers\ResponseHelper;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\AuthService;// Import UserService
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function usersList(Request $request): JsonResponse
    {
        try {
            // Extract filters from the request
            $filters = $request->only(['keyword', 'status', 'email_verified_at', 'order_by', 'order_direction', 'per_page', 'role']);
            // order_direction: asc or desc

            // Call the service method to get the filtered user list
            return $this->userService->usersList($filters);
        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return ResponseHelper::error('Failed to retrieve user list: ' . $e->getMessage(), 500);
        }
    }

    public function getProfile(): JsonResponse
    {
        try {
            // Call the service method to get the user profile
            return $this->userService->getProfile();
        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return ResponseHelper::error('Failed to retrieve user profile: ' . $e->getMessage(), 500);
        }
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $userDTO = UpdateUserDto::from($request->validated());
            return $this->userService->updateProfile($userDTO);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }

    public function updateEmailVerification(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        try {
            // Use UserService to update email verification status
            $this->userService->updateEmailVerification($user, $validated['email_verified']);
            return ResponseHelper::success(null, 'Email verification status updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        try {
            // Use UserService to update user status
            $this->userService->updateUserStatus($user, $validated['status']);
            return ResponseHelper::success(null, 'User status updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function updateUserInfo(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        try {
            // Extract fields that should be updated
            $updateData = [
                'name' => $validated['name'] ?? $user->name,
                'email' => $validated['email'] ?? $user->email,
                'password' => $validated['password'] ?? $user->password,
                'status' => $validated['status'] ?? $user->status,
                'email_verified_at' => $validated['email_verified_at'] ?? $user->email_verified_at,
            ];

            // Use UserService to update user information
            $this->userService->updateUserInfo($user, $updateData);
            return ResponseHelper::success($user, 'User information updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function getUserById($id): JsonResponse
    {
        try {
            $user = $this->userService->getUserWithTrashed($id);

            if (!$user) {
                return ResponseHelper::error('User not found.', 404);
            }

            return ResponseHelper::success($user, 'User retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function restoreUser($id)
{
    $user = User::onlyTrashed()->find($id);

    if ($user) {
        $user->restore();
        return ResponseHelper::success($user, 'User restored successfully.');
    } else {
        return ResponseHelper::error('User not found.', 404);
    }
}

    public function getAllUsersWithTrashed(): JsonResponse
    {
        try {
            $users = $this->userService->getAllUsersWithTrashed();
            return ResponseHelper::success($users, 'Users retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function softDeleteUser(User $user): JsonResponse
    {
        try {
            // Use UserService to soft delete user
            $this->userService->softDeleteUser($user);
            return ResponseHelper::success(null, 'User soft deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function hardDeleteUser(User $user): JsonResponse
    {
        try {
            // Use UserService to hard delete user
            $this->userService->hardDeleteUser($user);
            return ResponseHelper::success(null, 'User hard deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function updateUserPassword(UpdatePasswordRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        try {
            // Use UserService to update user's password
            $this->userService->updateUserPassword($user, $validated['password']);
            return ResponseHelper::success(null, 'User password updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
