<?php

namespace App\Http\Controllers\Client;

use App\DTOs\UpdateUserDto;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Exception;

class ClientController extends Controller
{
    protected ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Retrieve the authenticated user's profile.
     *
     * @return JsonResponse
     */
    public function getClientProfile(): JsonResponse
    {
        try {
            // Call the service method to get the user profile
            return $this->clientService->getProfile();
        } catch (Exception $e) {
            // Handle the exception and return an error response
            return ResponseHelper::error('Failed to retrieve user profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the client's profile.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateClientProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            // Create a DTO from validated request data
            $userDTO = UpdateUserDto::from($request->validated());

            // Call the service method to update the profile
            return $this->clientService->updateProfile($userDTO);
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }
}
