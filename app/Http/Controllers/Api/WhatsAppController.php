<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessResource;
use App\Services\BarberService;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WhatsAppController extends Controller
{
    public function __construct(
        private readonly BarberService $barberService
    ) {}

    public function listClients(): JsonResponse
    {
        $clients = $this->barberService->listClients();
        return SuccessResource::toJson($clients, 'Clients retrieved successfully');
    }

    public function createClient(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:clients,phone'
        ]);

        $result = $this->barberService->registerClient(
            $request->input('name'),
            $request->input('phone')
        );

        return SuccessResource::toJson($result, 'Client created successfully');
    }

    public function addCut(Client $client): JsonResponse
    {
        $result = $this->barberService->addCut($client->name);
        return SuccessResource::toJson($result, 'Cut added successfully');
    }

    public function useFreeCut(Client $client): JsonResponse
    {
        $result = $this->barberService->useFreeCut($client->name);
        return SuccessResource::toJson($result, 'Free cut used successfully');
    }

    public function stats(): JsonResponse
    {
        $stats = $this->barberService->getStats();
        return SuccessResource::toJson($stats, 'Stats retrieved successfully');
    }
}
