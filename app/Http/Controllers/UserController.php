<?php

namespace App\Http\Controllers;

use App\DTOs\UpdateUserDto;
use App\DTOs\UserDTO;
use App\Helpers\ResponseHelper;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
}
