<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function create(array $data): User
    {
        try {
            return User::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            throw new \Exception('Failed to create user.');
        }
    }

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User|null
     * @throws ModelNotFoundException
     */
    public function findByEmail(string $email): User
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                throw new ModelNotFoundException('User not found with email: ' . $email);
            }

            return $user;
        } catch (ModelNotFoundException $e) {
            Log::error($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error finding user by email: ' . $e->getMessage());
            throw new \Exception('Failed to find user by email.');
        }
    }

    /**
     * Get a paginated list of users with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     * @throws \Exception
     */
    public function usersList(array $filters): LengthAwarePaginator
    {
        try {
            $query = User::query();

            // Apply keyword search
            if (!empty($filters['keyword'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['keyword'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['keyword'] . '%');
                });
            }

            // Filter by status
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // Filter by email verified
            if (isset($filters['email_verified_at'])) {
                if ($filters['email_verified_at']) {
                    $query->whereNotNull('email_verified_at');
                } else {
                    $query->whereNull('email_verified_at');
                }
            }

            // Filter by role
            if (!empty($filters['role'])) {
                $query->where('role', $filters['role']);
            }

            // Apply ordering
            $orderBy = $filters['order_by'] ?? 'created_at';
            $orderDirection = $filters['order_direction'] ?? 'desc'; // asc or desc
            $query->orderBy($orderBy, $orderDirection);

            // Apply pagination
            $perPage = $filters['per_page'] ?? 15;

            return $query->paginate($perPage)->appends($filters);
        } catch (\Exception $e) {
            Log::error('Error fetching users list: ' . $e->getMessage());
            throw new \Exception('Failed to fetch users list.');
        }
    }

     /**
     * Update the given user with the provided data.
     *
     * @param User $user
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function update(User $user, array $data): User
    {
        try {

            // Filter out null values to avoid overwriting fields with null
            $updateData = array_filter($data, function ($value) {
                return !is_null($value);
            });

            if($data['email_verified_at']==null){
                $updateData['email_verified_at']=null;
            }

            // Update user information
            $user->update($updateData);

            return $user;
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            throw new \Exception('Failed to update user.');
        }
    }

    public function softDelete(User $user): void
    {
        try {
            // Soft delete the user
            $user->delete();
        } catch (\Exception $e) {
            Log::error('Error soft deleting user: ' . $e->getMessage());
            throw new \Exception('Failed to soft delete user.');
        }
    }

    public function findWithTrashed($id): ?User
    {
        return User::withTrashed()->find($id);
    }

    public function getAllUsersWithTrashed()
    {
        return User::withTrashed()->get();
    }

    public function findById($id): ?User
    {
        return User::find($id);
    }

}
