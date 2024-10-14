<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Services\NodeApiService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NodesController extends Controller
{
    protected $nodeApiService;

    public function __construct(NodeApiService $nodeApiService)
    {
        $this->nodeApiService = $nodeApiService;
    }

    public function syncNodes(Request $request)
    {
        $clientName = $request->input('client_name');
        if (!$clientName) {
            return ResponseHelper::error('Client name is required', 400);
        }

        try {
            $response = $this->nodeApiService->getMeshData($clientName);

            if ($response['success']) {
                $meshData = $response['data']['meshData'];

                foreach ($meshData as $mesh) {
                    $meshid = $mesh['meshid'];
                    $meshnodes = $mesh['meshnodes'];
                    $meshdoc = $mesh['meshdoc'];
                    $mesh_name = $meshdoc['name'];
                    $childClientName = $mesh['child_client_name'];

                    foreach ($meshnodes as $node) {

                        $node_name = $node['doc']['name'];
                        Node::updateOrCreate(
                            ['nodeid' => $node['nodeid']],
                            [
                                'meshid' => $meshid,
                                'mesh_name'=>$mesh_name,
                                'node_name'=>$node_name,
                                'client_remotik_id' => $clientName,
                                'is_child_node' => $childClientName ? true : false,
                                'child_client_remotik_id' => $childClientName
                            ]
                        );
                    }
                }

                return ResponseHelper::success(null,'Nodes synced successfully');
            } else {
                return ResponseHelper::error('Failed to retrieve mesh data', 500);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ResponseHelper::error('An error occurred while syncing nodes: ' . $e->getMessage(), 500);
        }
    }

    public function getNodes(Request $request)
    {
        try {
            // Initialize the query
            $query = Node::query();

            // Apply filters based on request parameters
            if ($request->has('meshid')) {
                $query->where('meshid', $request->input('meshid'));
            }

            if ($request->has('nodeid')) {
                $query->where('nodeid', $request->input('nodeid'));
            }

            if ($request->has('client_remotik_id')) {
                $query->where('client_remotik_id', $request->input('client_remotik_id'));
            }

            if ($request->has('child_client_remotik_id')) {
                $query->where('child_client_remotik_id', $request->input('child_client_remotik_id'));
            }

            // Execute the query and fetch the filtered data
            $nodes = $query->get();

            // Check if any data was found
            if ($nodes->isEmpty()) {
                return ResponseHelper::error('No nodes found', 404);
            }

            // Return success response with filtered nodes
            return ResponseHelper::success($nodes, 'Nodes retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('An error occurred while fetching nodes: ' . $e->getMessage(), 500);
        }
    }

}
