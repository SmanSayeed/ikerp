<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClientResource;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Models\Client;
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




    public function getChildClientProfile($client_remotik_id,$child_client_remotik_id)
    {
        try {
            $client = Client::where('client_remotik_id', $child_client_remotik_id)->where('parent_client_id',$client_remotik_id)->first();

            if (!$client) {
                return ResponseHelper::error('Client not found.', 404);
            }

            return ResponseHelper::success(new ClientResource($client), 'Client retrieved successfully.');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
