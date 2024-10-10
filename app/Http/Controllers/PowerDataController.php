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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // Import the HTTP client

class PowerDataController extends Controller
{


    public function syncSqlite(Request $request)
    {
        $client = Client::where('client_remotik_id', $request->input('client_remotik_id'))->first();
        if (!$client) {
            throw new \Exception('Client not found');
        }

        try {
            // Call the Node.js API to fetch power data
            $response = Http::get('http://localhost:3000/api/power/client', [
                'username' => $client->client_remotik_id, // Assuming username is fetched from client
            ]);

            // Check if the API call was successful
            if ($response->successful()) {
                $data = $response->json('data'); // Extract the 'data' array from the response
                $newRecords = 0; // Counter for new records

                foreach ($data as $item) {
                    $remotik_power_id = $item['remotik_power_id'];

                    // Check if the remotik_power_id already exists
                    $existingEntry = PowerData::where('remotik_power_id', $remotik_power_id)->first();

                    if ($existingEntry) {
                        continue; // Skip if the ID already exists
                    }

                    // Ensure the necessary fields exist
                    if (isset($item['time']) && isset($item['nodeid']) && isset($item['power'])) {
                        // Parse the datetime
                        $dateTime = Carbon::parse($item['time'])->toDateTimeString();

                        // Prepare the data for insertion
                        $nodeid = $item['nodeid'];
                        if ($nodeid == "*") {
                            continue; // Skip if nodeid is '*'
                        }

                        // Prepare the data for insertion
                        $createData = [
                            'remotik_power_id' => $remotik_power_id,
                            'time' => $dateTime,
                            'nodeid' => $nodeid,
                            'node_name' => $item['node_name'], // Assuming node_name is present in the API response
                            'power' => $item['power'],
                            'client_id' => $client->id,
                            'client_remotik_id'=>$client->client_remotik_id
                        ];

                        // Insert the data into MySQL
                        PowerData::create($createData);
                        $newRecords++;
                    }
                }

                // Update client's last synced time
                $client->update([
                    'last_synced' => Carbon::now(),
                ]);

                // Return success response based on new records
                if ($newRecords > 0) {
                    return ResponseHelper::success(
                        ['new_records' => $newRecords],
                        "$newRecords new record(s) have been synced successfully for client {$client->name}."
                    );
                } else {
                    return ResponseHelper::success(
                        null,
                        'No new data to sync.'
                    );
                }
            } else {
                // Handle failed API response
                throw new \Exception('Failed to retrieve power data from the Node.js API.');
            }
        } catch (\Exception $e) {
            // Handle any exceptions
            Log::error($e->getMessage());
            return ResponseHelper::error(
                'An error occurred while syncing data: ' . $e->getMessage(),
                500
            );
        }
    }

}
