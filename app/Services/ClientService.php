<?php

namespace App\Services;

use App\Models\Client;
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
                $query->where('role', $filters['role']);
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

    public function getProfile(): JsonResponse
    {
        try {
            $client = Auth::user();

            if (!$client) {
                return ResponseHelper::error('Client not authenticated.', 401);
            }

            return ResponseHelper::success($client, 'Client profile retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve client profile: ' . $e->getMessage(), 500);
        }
    }

    public function updateProfile(Client $client, array $data): JsonResponse
    {
        try {
            $client->update($data);
            return ResponseHelper::success($client, 'Profile updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }

    public function updateEmailVerification(Client $client, bool $verified): JsonResponse
    {
        try {
            $client->email_verified_at = $verified ? now() : null;
            $client->save();
            return ResponseHelper::success($client, 'Email verification updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update email verification: ' . $e->getMessage(), 500);
        }
    }

    public function updateClientStatus(Client $client, bool $status): JsonResponse
    {
        try {
            $client->status = $status;
            $client->save();
            return ResponseHelper::success($client, 'Client status updated successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to update client status: ' . $e->getMessage(), 500);
        }
    }

    public function updateClientInfo(Client $client, array $data): JsonResponse
    {
        try {
            $client->update($data);
            return ResponseHelper::success($client, 'Client information updated successfully.');
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
