<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\UpdateClientDto;
use App\DTOs\ClientDTO;
use App\Events\SendEmail;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterClientRequest;
use App\Http\Requests\UpdatePasswordByEmailRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Services\AuthService;// Import ClientService
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Models\Client;




class AdminManagesClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function clientsList(Request $request): JsonResponse
    {
        try {
            // Extract filters from the request
            $filters = $request->only(['keyword', 'status', 'email_verified_at', 'order_by', 'order_direction', 'per_page', 'role']);
            // order_direction: asc or desc

            // Call the service method to get the filtered client list
            return $this->clientService->clientsList($filters);
        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return ResponseHelper::error('Failed to retrieve client list: ' . $e->getMessage(), 500);
        }
    }

    public function getProfile(): JsonResponse
    {
        try {
            // Call the service method to get the client profile
            return $this->clientService->getProfile();
        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return ResponseHelper::error('Failed to retrieve client profile: ' . $e->getMessage(), 500);
        }
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $clientDTO = UpdateClientDto::from($request->validated());
            return $this->clientService->updateProfile($clientDTO);
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }

    public function updateEmailVerification(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $validated = $request->validated();

        try {
            // Use ClientService to update email verification status
            $this->clientService->updateEmailVerification($client, $validated['email_verified']);
            return ResponseHelper::success(null, 'Email verification status updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function updateStatus(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validated();

        try {
            // Use ClientService to update client status
            $this->clientService->updateClientStatus($client, $validated['status']);
            return ResponseHelper::success(null, 'Client status updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function updateClientInfo(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $validated = $request->validated();

        try {
            // Extract fields that should be updated
            $updateData = [
                'name' => $validated['name'] ?? $client->name,
                'email' => $validated['email'] ?? $client->email,
                'password' => $validated['password'] ?? $client->password,
                'status' => $validated['status'] ?? $client->status,
                'email_verified_at' => $validated['email_verified_at'] ?? $client->email_verified_at,
            ];

            // Use ClientService to update client information
            $this->clientService->updateClientInfo($client, $updateData);
            return ResponseHelper::success($client, 'Client information updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function getClientById($id): JsonResponse
    {
        try {
            $client = $this->clientService->getClientWithTrashed($id);

            if (!$client) {
                return ResponseHelper::error('Client not found.', 404);
            }

            return ResponseHelper::success($client, 'Client retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function restoreClient($id)
{
    $client = Client::onlyTrashed()->find($id);

    if ($client) {
        $client->restore();
        return ResponseHelper::success($client, 'Client restored successfully.');
    } else {
        return ResponseHelper::error('Client not found.', 404);
    }
}

    public function getAllClientsWithTrashed(): JsonResponse
    {
        try {
            $clients = $this->clientService->getAllClientsWithTrashed();
            return ResponseHelper::success($clients, 'Clients retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function softDeleteClient(Client $client): JsonResponse
    {
        try {
            // Use ClientService to soft delete client
            $this->clientService->softDeleteClient($client);
            return ResponseHelper::success(null, 'Client soft deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function hardDeleteClient(Client $client): JsonResponse
    {
        try {
            // Use ClientService to hard delete client
            $this->clientService->hardDeleteClient($client);
            return ResponseHelper::success(null, 'Client hard deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function updateClientPassword(UpdatePasswordRequest $request, Client $client): JsonResponse
    {
        $validated = $request->validated();

        try {
            if (!\Illuminate\Support\Facades\Hash::check($validated['old_password'], $client->password)) {
                return ResponseHelper::error('Old password does not match.', 400);
            }
            // Use ClientService to update client's password
            $this->clientService->updateClientPassword($client, $validated['password']);
            return ResponseHelper::success(null, 'Client password updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }



}
