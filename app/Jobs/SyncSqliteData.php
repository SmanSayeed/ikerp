<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PowerData;
use App\Models\Node;
use App\Models\PowerDataSyncLog;
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
        $totalInserted = 0; // Count of new records inserted
        $status = 'no_new_data'; // Default status
        $logMessage = 'Sync complete: No new data was inserted.'; // Default message

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

                                // Generate unique_id by concatenating time, nodeid, and remotik_power_id
                                $uniqueId = $data['time'] . '_' . $data['nodeid'] . '_' . $data['id'];

                                // Check if this unique_id already exists
                                if (PowerData::where('unique_id', $uniqueId)->exists()) {
                                    continue; // Skip if the unique_id already exists
                                }

                                // Prepare data for batch insert
                                $insertData[] = [
                                    'unique_id' => $uniqueId,
                                    'remotik_power_id' => $data['id'],
                                    'time' => $dateTime,
                                    'nodeid' => $data['nodeid'],
                                    'power' => $data['power'],
                                    'client_remotik_id' => $this->clientRemotikId,
                                    'child_client_remotik_id' => $node->child_client_remotik_id,
                                    'is_parent' => !$node->is_child_node,
                                    'is_child' => $node->is_child_node,
                                    'node_name' => $node->node_name,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            } catch (\Exception $e) {
                                // Log error during processing
                                $errorMessage = 'Error processing nodeid: ' . $data['nodeid'] . ' - ' . $e->getMessage();
                                Log::error($errorMessage);
                                $this->powerDataSyncLog('error', $errorMessage);
                            }
                        }

                        try {
                            // Insert the batch data into the MySQL database
                            if (!empty($insertData)) {
                                PowerData::insert($insertData);
                                $totalInserted += count($insertData);
                            }
                        } catch (\Exception $e) {
                            // Log errors during insertion
                            $errorMessage = 'Error inserting batch data for nodeid: ' . $nodeid . ' - ' . $e->getMessage();
                            Log::error($errorMessage);
                            $this->powerDataSyncLog('error', $errorMessage);
                        }
                    }

                    // Update the log message after successful processing
                    if ($totalInserted > 0) {
                        $status = 'new_data';
                        $logMessage = "$totalInserted new records inserted.";
                    }
                } else {
                    // Log error if API response is not successful
                    $errorMessage = 'Failed to retrieve power data for node ' . $nodeid . ': ' . $response['message'];
                    Log::error($errorMessage);
                    $this->powerDataSyncLog('error', $errorMessage);
                }
            } else {
                // Log error if the node is not found in the database
                $errorMessage = 'Node not found for nodeid: ' . $nodeid;
                Log::error($errorMessage);
                $this->powerDataSyncLog('error', $errorMessage);
            }
        }

        // Log the sync summary
        $this->powerDataSyncLog($status, $logMessage, $totalInserted);
    }

    public function powerDataSyncLog($status, $message, $totalInserted = 0)
    {
        // Log the sync result for this node
        try {
            PowerDataSyncLog::create([
                'client_remotik_id' => $this->clientRemotikId,
                'synced_count' => $totalInserted,
                'status' => $status,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            // Log any errors during the creation of the sync log
            Log::error('Error creating PowerDataSyncLog: ' . $e->getMessage());
        }
    }
}
