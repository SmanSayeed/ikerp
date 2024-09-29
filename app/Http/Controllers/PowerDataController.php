<?php
// app/Http/Controllers/PowerDataController.php

namespace App\Http\Controllers;

use App\Models\PowerData;
use App\Models\SqliteModelPower;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PowerDataController extends Controller
{
    public function syncSqlite()
    {
        // Fetch all data from the SQLite model
        $data = SqliteModelPower::all()->toArray(); // Convert the collection to an array

        foreach ($data as $item) {
            // Ensure 'doc' and 'time' fields exist
            if (isset($item['doc']['time']) && isset($item['doc']['nodeid']) && isset($item['doc']['power'])) {
                // Convert the ISO 8601 formatted time to Carbon instance
                $dateTime = Carbon::parse($item['doc']['time'])->toDateTimeString();

                // Prepare the data for insertion
                $createData = [
                    'time' => $dateTime,  // Correctly formatted datetime
                    'nodeid' => $item['doc']['nodeid'],  // Ensure it's properly formatted
                    'power' => $item['doc']['power'],
                    'client_id' => 1,  // Assuming client_id is always 1
                ];

                // Insert into the database
                PowerData::create($createData);
            }
        }

        return response()->json(['message' => 'Data has been synced successfully.']);
    }
}
