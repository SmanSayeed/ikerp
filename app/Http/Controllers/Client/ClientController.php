<?php

namespace App\Http\Controllers\Client;

use App\DTOs\UpdateClientDto;
use App\DTOs\UpdateUserDto;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ClientResource;
use App\Services\ClientService;
use App\Services\NodeApiService;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Models\Client;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
            $client = $this->clientService->getProfile();
            return ResponseHelper::success(['client'=>new ClientResource($client)], 'Client profile retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve client profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the client's profile.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateClientProfile(UpdateClientRequest $request): JsonResponse
    {
        try {
            $clientDTO = UpdateClientDto::from($request->validated());
            $client = $this->clientService->updateProfile($clientDTO);
            return ResponseHelper::success(['client'=>new ClientResource($client)], 'Profile updated successfully.');
        } catch (ValidationException $e) {
            return ResponseHelper::error('Validation failed: ' . $e->getMessage(), 422);
        } catch (ModelNotFoundException $e) {
            return ResponseHelper::error('Client not found: ' . $e->getMessage(), 404);
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }

    public function getClientsFromNodeJS()
    {
        // Validate the request to ensure client_remotik_id is provided

        // Use the NodeApiService to get child clients
        $nodeApiService = new NodeApiService();
        $response =$nodeApiService->getClients();

        // Check if the response is successful
        if ($response['success']) {
            return ResponseHelper::success($response['data'], 'Clients retrieved successfully');
        }

        return ResponseHelper::error($response['message'], 500);
    }

    public function getClientsArray()
    {
        // Validate the request to ensure client_remotik_id is provided

        // Use the NodeApiService to get child clients
        $clients = Client::select('client_remotik_id')->where('is_child', 0)->get();
         $array = [];
        foreach($clients as $client) {
            $array[] = $client->client_remotik_id;
        }
        $response = $array;

        // Check if the response is successful
        if ($response) {
            return ResponseHelper::success($response, 'Clients retrieved successfully');
        }

        return ResponseHelper::error($response['message'], 500);
    }
}
