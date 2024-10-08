<?php

namespace App\Services;

use App\DTOs\UpdateClientDto;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Hash;

class ClientService
{
    public function clientsList(array $filters): JsonResponse
    {
        try {
            // Apply filters and retrieve clients from the database
            $query = Client::query();

            if (isset($filters['keyword'])) {
                $query->where('name', 'like', '%' . $filters['keyword'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['keyword'] . '%');
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['email_verified_at'])) {
                $query->whereNotNull('email_verified_at');
            }

            if (isset($filters['role'])) {
                $query->where('is_seller', $filters['role']); // Adjust role to is_seller
            }

            $orderBy = $filters['order_by'] ?? 'id';
            $orderDirection = $filters['order_direction'] ?? 'asc';
            $query->orderBy($orderBy, $orderDirection);

            $perPage = $filters['per_page'] ?? 15;
            $clients = $query->paginate($perPage);

            return ResponseHelper::success($clients, 'Clients retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve clients: ' . $e->getMessage(), 500);
        }
    }

    public function getProfile(): ?Authenticatable
    {
        try {
            $client = Auth::user();

            if (!$client) {
                return null;
            }

            return $client;
        } catch (Exception $e) {
            return null;
        }
    }

    public function updateProfile(UpdateClientDto $clientDTO): ?Authenticatable
    {
        try {
            $client = Auth::user();
            if (!$client) {
                return null;
            }

            $client->update($clientDTO->toArray());
            return $client;
        } catch (Exception $e) {
            return null;
        }
    }

    public function updateEmailVerification(Client $client, bool $verified): JsonResponse
    {
        try {
            $client->email_verified_at = $verified ? now() : null;
            $client->save();
            return ResponseHelper::success(new ClientResource($client), 'Email verification updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update email verification: ' . $e->getMessage(), 500);
        }
    }

    public function updateClientStatus(Client $client, bool $status): JsonResponse
    {
        try {
            $client->status = $status;
            $client->save();
            return ResponseHelper::success(new ClientResource($client), 'Client status updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update client status: ' . $e->getMessage(), 500);
        }
    }

    public function updateClientInfo(Client $client, array $data): JsonResponse
    {
        try {
            $client->update($data);
            return ResponseHelper::success(new ClientResource($client), 'Client information updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update client information: ' . $e->getMessage(), 500);
        }
    }

    public function getClientWithTrashed($id): ?Client
    {
        return Client::withTrashed()->find($id);
    }

    public function getAllClientsWithTrashed(): JsonResponse
    {
        try {
            $clients = Client::withTrashed()->get();
            return ResponseHelper::success($clients, 'Clients retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve clients: ' . $e->getMessage(), 500);
        }
    }

    public function softDeleteClient(Client $client): JsonResponse
    {
        try {
            $client->delete();
            return ResponseHelper::success(null, 'Client soft deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to soft delete client: ' . $e->getMessage(), 500);
        }
    }

    public function hardDeleteClient(Client $client): JsonResponse
    {
        try {
            $client->forceDelete();
            return ResponseHelper::success(null, 'Client hard deleted successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to hard delete client: ' . $e->getMessage(), 500);
        }
    }

    public function updateClientPassword(Client $client, string $password): JsonResponse
    {
        try {
            $client->password = Hash::make($password);
            $client->save();
            return ResponseHelper::success(null, 'Password updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update password: ' . $e->getMessage(), 500);
        }
    }
}
