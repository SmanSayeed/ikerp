<?php

namespace App\Services;

use App\DTOs\UpdateUserDto;
use App\DTOs\UserDto;
use App\Repositories\UserRepositoryInterface;
use Exception;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class UserService
{
    public function __construct(private UserRepositoryInterface $userRepository) {}


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
        $user = auth()->user(); // Get the currently authenticated user
        // Update user details
        $user->name = $userDTO->name;
        $user->save();

        return ResponseHelper::success($user, 'Profile updated successfully');
    }
    public function usersList():JsonResponse
    {
        $data =  $this->userRepository->usersList();
        return ResponseHelper::success($data, 'User list fetched successfully.');
    }
}
