<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Services\NodeApiService;

class ChildClientController extends Controller
{
    protected $nodeApiService;

    public function __construct(NodeApiService $nodeApiService)
    {
        $this->nodeApiService = $nodeApiService;
    }

    public function getChildClients(Request $request)
    {
        // Validate the request to ensure client_remotik_id is provided
        $request->validate([
            'client_remotik_id' => 'required|string'
        ]);

        $client_remotik_id = $request->input('client_remotik_id');

        // Use the NodeApiService to get child clients
        $response = $this->nodeApiService->getChildClients($client_remotik_id);

        // Check if the response is successful
        if ($response['success']) {
            return ResponseHelper::success($response['data'], 'Child clients retrieved successfully');
        }

        return ResponseHelper::error($response['message'], 500);
    }
}
