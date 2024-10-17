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

    /**
     * Create a new job instance.
     */
    public function __construct($clientRemotikId, $nodesToSync, NodeApiService $nodeApiService)
    {
        $this->clientRemotikId = $clientRemotikId;
        $this->nodesToSync = $nodesToSync; // Array of node IDs to sync
        $this->nodeApiService = $nodeApiService;
    }

    /**
     * Execute the job.
     */
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

                    // Split into smaller batches to handle large datasets efficiently
                    $chunks = array_chunk($powerData, 1000); // Adjust batch size as needed

                    foreach ($chunks as $batch) {
                        $insertData = [];

                        foreach ($batch as $data) {
                            try {
                                // Parse the time (from milliseconds) and convert to MySQL compatible format
                                $dateTime = Carbon::createFromTimestampMs($data['time'])->toDateTimeString();

                                // Prepare data for batch insert
                                $insertData[] = [
                                    'remotik_power_id' => $data['id'],
                                    'time' => $dateTime, // Use formatted datetime
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
                            } catch (\Exception $e) {
                                // Log any error during processing
                                Log::error('Error processing nodeid: ' . $data['nodeid'] . ' - ' . $e->getMessage());
                            }
                        }

                        try {
                            // Insert the batch data into the MySQL database
                            PowerData::insert($insertData);
                        } catch (\Exception $e) {
                            // Log errors during insertion
                            Log::error('Error inserting batch data for nodeid: ' . $nodeid . ' - ' . $e->getMessage());
                        }
                    }
                } else {
                    // Log error if API response is not successful
                    Log::error('Failed to retrieve power data for node ' . $nodeid . ': ' . $response['message']);
                }
            } else {
                // Log error if the node is not found in the database
                Log::error('Node not found for nodeid: ' . $nodeid);
            }
        }
    }
}
