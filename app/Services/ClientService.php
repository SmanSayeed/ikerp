<?php

namespace App\Services;

use App\DTOs\UpdateUserDto;
use App\Helpers\ResponseHelper;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

class ClientService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Retrieve the authenticated user's profile.
     *
     * @return JsonResponse
     */
    public function getProfile(): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = Auth::user();

            // Check if the user is authenticated
            if (!$user) {
                return ResponseHelper::error('User not authenticated.', 401);
            }

            // Return the user data
            return ResponseHelper::success($user, 'User profile retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve user profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the user's profile.
     *
     * @param UpdateUserDto $userDTO
     * @return JsonResponse
     */
    public function updateProfile(UpdateUserDto $userDTO): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = Auth::user();

            // Check if the user is authenticated
            if (!$user) {
                return ResponseHelper::error('User not authenticated.', 401);
            }

            // Update the user's profile data
            $user->name = $userDTO->name;
            // You can update other fields from the DTO as well
            $user->save();

            // Return the updated user data
            return ResponseHelper::success($user, 'Profile updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }
}
