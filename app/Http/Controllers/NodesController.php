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

            // Apply filters only if request parameters are present
            if ($request->filled('mesh_name')) {
                $query->where('mesh_name', $request->input('mesh_name'));
            }

            if ($request->filled('node_name')) {
                $query->where('node_name', $request->input('node_name'));
            }

            if ($request->filled('client_remotik_id')) {
                $query->where('client_remotik_id', $request->input('client_remotik_id'));
            }

            if ($request->filled('child_client_remotik_id')) {
                $query->where('child_client_remotik_id', $request->input('child_client_remotik_id'));
            }

            // Execute the query and fetch the filtered data
            $nodes = $query->get();

            // Return success response with filtered nodes
            return ResponseHelper::success($nodes, 'Nodes retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error('An error occurred while fetching nodes: ' . $e->getMessage(), 500);
        }
    }

}
