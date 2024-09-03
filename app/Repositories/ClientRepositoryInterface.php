<?php

namespace App\Repositories;
use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\PaginatedDataCollection;

use Illuminate\Pagination\LengthAwarePaginator;
interface ClientRepositoryInterface
{
    public function create(array $data):Client;
    public function findClientByEmail(string $email):Client;
    public function clientsList(array $filters):LengthAwarePaginator;
    public function updateClient(Client $user, array $data): Client;

    public function softDelete(Client $user): void;

    public function findWithTrashed($id): ?Client;

    public function getAllClientsWithTrashed();
    public function findById($id): ?Client;

}
