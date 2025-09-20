<?php

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

class ClientRepository
{
    /**
     * Pega o nome do cliente
     *
     * @param string $name
     * @return Client|null
     */
    public function findByName(string $name): ?Client
    {
        return Client::where('name', 'ILIKE', '%' . $name . '%')->first();
    }

    /**
     * @param string $phone
     * @return Client|null
     */
    public function findByPhone(string $phone): ?Client
    {
        return Client::where('phone', $phone)->first();
    }

    public function getAll(): Collection
    {
        return Client::orderBy('name')->get();
    }

    public function create(array $data): Client
    {
        return Client::create($data);
    }

    public function getTopClients(int $limit = 5): Collection
    {
        return Client::orderByDesc('cuts_count')->take($limit)->get();
    }

    public function getAlmostFreeClients(): Collection
    {
        return Client::whereRaw('(cuts_count % 10) >= 8')->get();
    }

    public function getStats(): array
    {
        return [
            'total_clients' => Client::count(),
            'total_cuts' => Client::sum('cuts_count'),
            'total_free_cuts' => Client::sum('free_cuts_earned'),
            'average_cuts' => Client::count() > 0 ? round(Client::sum('cuts_count') / Client::count(), 1) : 0
        ];
    }
}
