<?php

namespace App\Repositories;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\PaginatedDataCollection;

use Illuminate\Pagination\LengthAwarePaginator;
interface UserRepositoryInterface
{
    public function create(array $data):User;
    public function findByEmail(string $email):User;
    public function usersList(array $filters):LengthAwarePaginator;
    public function update(User $user, array $data): User;
}
