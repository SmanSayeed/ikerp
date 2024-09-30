<?php
// app/Http/Controllers/PowerDataController.php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\PowerData;
use App\Models\SqliteModelPower;
use App\Models\SqliteModelMain; // Import the model
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Helpers\ResponseHelper; // Helper for handling responses

class PowerDataController extends Controller
{
    public function syncSqlite(Request $request)
    {
        $client = Client::find($request->input('client_id'));
        if(!$client) {
            throw new \Exception('Client not found');
        }

        try {
            // Fetch all data from the SQLite model
            $data = SqliteModelPower::all()->toArray(); // Convert the collection to an array
            $newRecords = 0; // Counter for the number of new records inserted

            foreach ($data as $item) {
                $remotik_power_id = $item['id'];

                // Check if remotik_power_id already exists in the power_data table
                $existingEntry = PowerData::where('remotik_power_id', $remotik_power_id)->first();

                if ($existingEntry) {
                    // If the remotik_power_id already exists, skip this entry
                    continue;
                }

                // Ensure 'doc' and 'time' fields exist
                if (isset($item['doc']['time']) && isset($item['doc']['nodeid']) && isset($item['doc']['power'])) {
                    // Convert the ISO 8601 formatted time to Carbon instance
                    $dateTime = Carbon::parse($item['doc']['time'])->toDateTimeString();

                    // Prepare the data for insertion
                    $nodeid = $item['doc']['nodeid']; // Fetch nodeid
                    if ($nodeid == "*") {
                        // If the nodeid is '*', skip it
                        continue;
                    }

                    // Fetch node name from SqliteModelMain based on nodeid
                    $node = SqliteModelMain::where('type', 'node')
                        ->where('id', $nodeid)
                        ->first();

                    // Set node_name based on the fetched node data
                    $nodeName = $node ? $node->doc["name"] : null; // Assign node name or null if not found

                    // Prepare the data to be inserted
                    $createData = [
                        'remotik_power_id' => $remotik_power_id,
                        'time' => $dateTime,  // Correctly formatted datetime
                        'nodeid' => $nodeid,  // Ensure it's properly formatted
                        'node_name' => $nodeName, // Insert the node name
                        'power' => $item['doc']['power'],
                        'client_id' => $client->id,  // Assuming client_id is always 1
                    ];

                    $client->update([
                        'last_synced'=>Carbon::now(),
                    ]);

                    // Insert the data into the power_data table
                    PowerData::create($createData);
                    $newRecords++; // Increment the counter for each new record inserted
                }
            }

            // Check if any new records were inserted
            if ($newRecords > 0) {
                return ResponseHelper::success(
                    ['new_records' => $newRecords],
                    "$newRecords new record(s) have been synced successfully for client {$client->name}."
                );
            } else {
                // No new data to sync
                return ResponseHelper::success(
                    null,
                    'No new data to sync.'
                );
            }
        } catch (\Exception $e) {
            // Handle any exception and return an error response
            return ResponseHelper::error(
                'An error occurred while syncing data: ' . $e->getMessage(),
                500
            );
        }
    }
}
