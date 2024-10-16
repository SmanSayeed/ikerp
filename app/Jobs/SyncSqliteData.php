<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PowerData;
use App\Models\Node;
use App\Services\NodeApiService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncSqliteData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $clientRemotikId;
    protected $nodesToSync;
    protected $nodeApiService;

    public function __construct($clientRemotikId, $nodesToSync, NodeApiService $nodeApiService)
    {
        $this->clientRemotikId = $clientRemotikId;
        $this->nodesToSync = $nodesToSync; // Array of node IDs to sync
        $this->nodeApiService = $nodeApiService;
    }

    public function handle()
    {
        foreach ($this->nodesToSync as $nodeid) {
            // Retrieve node information from the database
            $node = Node::where('nodeid', $nodeid)->first();

            if ($node) {
                // Make individual API call for each node ID and client_remotik_id
                $response = $this->nodeApiService->getPowerDataForNode($nodeid, $this->clientRemotikId);

                if ($response['success']) {
                    $powerData = $response['data'];

                    // Prepare data for batch insert
                    $insertData = [];
                    foreach ($powerData as $data) {
                        // Parse the datetime from API response and convert to MySQL format
                        $dateTime = Carbon::parse($data['time'])->toDateTimeString(); // Convert to MySQL-compatible format

                        $insertData[] = [
                            'remotik_power_id' => $data['id'],
                            'time' => $dateTime,  // Use formatted datetime
                            'nodeid' => $data['nodeid'],
                            'power' => $data['power'],
                            'client_remotik_id' => $this->clientRemotikId,
                            'child_client_remotik_id' => $node->child_client_remotik_id, // Set from node
                            'is_parent' => $node->is_child_node ? false : true,
                            'is_child' => $node->is_child_node ? true : false,
                            'node_name' => $node->node_name, // Set node_name from node data
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // Insert the power data into the MySQL database
                    PowerData::insert($insertData);
                } else {
                    Log::error('Failed to retrieve power data for node ' . $nodeid . ': ' . $response['message']);
                }
            } else {
                Log::error('Node not found for nodeid: ' . $nodeid);
            }
        }
    }
}
