<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\UpdateClientDto;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpdateClientRequest; // Updated request class
use App\Http\Resources\AdminManagesClientResource;
use App\Services\AdminManagesClientService; // Correct service class
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Client;
use Exception;
use Illuminate\Support\Facades\Hash;

class AdminManagesClientController extends Controller
{
    protected AdminManagesClientService $adminManagesClientService; // Correct type hint

    public function __construct(AdminManagesClientService $adminManagesClientService) // Correct service class
    {
        $this->adminManagesClientService = $adminManagesClientService;
    }

    public function clientsList(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['keyword', 'status', 'email_verified_at', 'order_by', 'order_direction', 'per_page', 'role','client_remotik_id']);
            return $this->adminManagesClientService->clientsList($filters);
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve client list: ' . $e->getMessage(), 500);
        }
    }

    public function updateEmailVerification(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'email_verified_at' => 'required|boolean',
        ]);

        try {
            $this->adminManagesClientService->updateEmailVerification($client, $validated['email_verified_at']);
            return ResponseHelper::success(null, 'Email verification status updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function updateStatus(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|boolean',
        ]);

        try {
            $this->adminManagesClientService->updateClientStatus($client, $validated['status']);
            return ResponseHelper::success(null, 'Client status updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function updateClientInfo(AdminUpdateClientRequest $request, Client $client): JsonResponse // Updated request class
    {
        $validated = $request->validated();

        try {
            $updateData = [
                'name' => $validated['name'] ?? $client->name,
                'address' => $validated['address'] ?? $client->address,
                'phone' => $validated['phone'] ?? $client->phone,
                'client_remotik_id'=> $validated['client_remotik_id'] ?? $client->client_remotik_id,
                'password' => isset($validated['password']) ? Hash::make($validated['password']) : $client->password,

                'is_seller' => $validated['is_seller'] ?? $client->is_seller,
                'payment_due_date' => $validated['payment_due_date'] ?? $client->payment_due_date,
                'vat_slab' => $validated['vat_slab'] ?? $client->vat_slab,
                'gbs_information' => $validated['gbs_information'] ?? $client->gbs_information,
                'is_vip' => $validated['is_vip'] ?? $client->is_vip,
                'vip_discount' => $validated['vip_discount'] ?? $client->vip_discount,
                'status' => $validated['status'] ?? $client->status,
                'email_verified_at' => $validated['email_verified_at'] ? now() : null,
            ];

            $this->adminManagesClientService->updateClientInfo($client, $updateData);
            return ResponseHelper::success(new AdminManagesClientResource($client), 'Client information updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function getClientById($id): JsonResponse
    {
        try {
            $client = $this->adminManagesClientService->getClientWithTrashed($id);

            if (!$client) {
                return ResponseHelper::error('Client not found.', 404);
            }

            return ResponseHelper::success(new AdminManagesClientResource($client), 'Client retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function restoreClient($id): JsonResponse
    {
        $client = Client::onlyTrashed()->find($id);

        if ($client) {
            $client->restore();
            return ResponseHelper::success(new AdminManagesClientResource($client), 'Client restored successfully.');
        } else {
            return ResponseHelper::error('Client not found.', 404);
        }
    }

    public function getAllClientsWithTrashed(): JsonResponse
    {
        try {
            $clients = $this->adminManagesClientService->getAllClientsWithTrashed();
            return ResponseHelper::success($clients, 'Clients retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function softDeleteClient(Client $client): JsonResponse
    {
        try {
            $this->adminManagesClientService->softDeleteClient($client);
            return ResponseHelper::success(null, 'Client soft deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function hardDeleteClient(Client $client): JsonResponse
    {
        try {
            $this->adminManagesClientService->hardDeleteClient($client);
            return ResponseHelper::success(null, 'Client hard deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function updateClientPassword(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validated();

        try {
            if (!\Illuminate\Support\Facades\Hash::check($validated['old_password'], $client->password)) {
                return ResponseHelper::error('Old password does not match.', 400);
            }
            $this->adminManagesClientService->updateClientPassword($client, $validated['password']);
            return ResponseHelper::success(null, 'Client password updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
