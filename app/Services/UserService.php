<?php

namespace App\Services;

use App\DTOs\UpdateUserDto;
use App\DTOs\UserDto;
use App\Repositories\UserRepositoryInterface;
use Exception;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class UserService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }


    /**
     * Get the authenticated user's profile.
     *
     * @return JsonResponse
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = Auth::user(); // Retrieve the currently authenticated user

            if (!$user) {
                return ResponseHelper::error('User not authenticated.', 401);
            }

            return ResponseHelper::success($user, 'User profile retrieved successfully.');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to retrieve user profile: ' . $e->getMessage());
        }
    }

    public function updateProfile(UpdateUserDto $userDTO)
    {
        try {
            // Get the currently authenticated user
            $user = auth()->user();

            // Update user details
            $user->name = $userDTO->name;
            $user->save();

            // Return success response
            return ResponseHelper::success($user, 'Profile updated successfully');
        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return ResponseHelper::error('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }

    public function usersList(array $filters): JsonResponse
    {
        try {
            // Attempt to retrieve the filtered user list
            $data = $this->userRepository->usersList($filters);

            // Return success response if everything goes well
            return ResponseHelper::success($data, 'User list fetched successfully.');
        } catch (Exception $e) {
            // Handle the exception and return an error response
            return ResponseHelper::error('Failed to retrieve user list: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update user information.
     *
     * @param User $user
     * @param array $data
     * @return JsonResponse
     */
    public function updateUserInfo(User $user, array $data): JsonResponse
    {
        try {
            // Check if email_verified_at is set to a boolean, then handle it accordingly
            if (isset($data['email_verified_at'])) {
                $data['email_verified_at'] = $data['email_verified_at'] ? now() : null;
            }

            // Use the repository to update user information
            $updatedUser = $this->userRepository->update($user, $data);

            return ResponseHelper::success($updatedUser, 'User information updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update user information: ' . $e->getMessage(), 500);
        }
    }

    public function updateUserStatus(User $user, bool $status): JsonResponse
    {
        try {
            // Update user status
            $user->status = $status;
            $user->save();
            return ResponseHelper::success(null, 'User status updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update user status: ' . $e->getMessage(), 500);
        }
    }

    public function updateEmailVerification(User $user, bool $emailVerified): JsonResponse
    {
        try {
            $user->email_verified_at = $emailVerified ? now() : null;
            $user->save();
            return ResponseHelper::success(null, 'Email verification status updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update email verification status: ' . $e->getMessage(), 500);
        }
    }

    public function softDeleteUser(User $user): JsonResponse
    {
        try {
            $user->delete();
            return ResponseHelper::success(null, 'User soft deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to soft delete user: ' . $e->getMessage(), 500);
        }
    }

    public function hardDeleteUser(User $user): JsonResponse
    {
        try {
            $user->forceDelete();
            return ResponseHelper::success(null, 'User hard deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to hard delete user: ' . $e->getMessage(), 500);
        }
    }

    public function updateUserPassword(User $user, string $password): JsonResponse
    {
        try {
            $user->password = bcrypt($password);
            $user->save();
            return ResponseHelper::success(null, 'User password updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update user password: ' . $e->getMessage(), 500);
        }
    }
}
