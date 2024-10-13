<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NodeApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('NODE_API_URL');
    }

    public function getChildClients(string $username)
    {
        try {
            // Call the Node.js API to fetch child clients
            $response = Http::get("{$this->baseUrl}client/child", [
                'username' => $username,
            ]);

            // Check if the response is successful
            if ($response->successful()) {
                return $response->json(); // Return the JSON response
            }

            // Handle non-successful responses (optional)
            return [
                'success' => false,
                'message' => 'Failed to fetch child clients',
                'data' => null
            ];
        } catch (\Exception $e) {
            // Handle exceptions and return a consistent error response
            return [
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function getPowerData(string $username)
    {
        try {
            // Call the Node.js API to fetch power data
            $response = Http::get("{$this->baseUrl}power/client-child", [
                'username' => $username,
            ]);

            // Check if the response is successful
            if ($response->successful()) {
                return $response->json(); // Return the JSON response
            }

            // Handle non-successful responses (optional)
            return [
                'success' => false,
                'message' => 'Failed to fetch power data',
                'data' => null
            ];
        } catch (\Exception $e) {
            // Handle exceptions and return a consistent error response
            return [
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }


}


