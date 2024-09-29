<?php

namespace App\Http\Controllers;

use App\Models\SqliteModelPower;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function generateInvoice(Request $request)
    {
        // Fetch all data from the database
        $data = SqliteModelPower::all();

        // Filter out the nodes that had power 1
        $poweredNodes = $data->filter(function ($item) {
            return $item->doc['power'] === 1;
        });

        // If there are any nodes with power 1, return those node IDs
        if ($poweredNodes->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Nodes with power 1 found',
                'nodes' => $poweredNodes->pluck('doc.nodeid'),
            ]);
        }

        // If no node had power 1, return this response
        return response()->json([
            'status' => 'success',
            'message' => 'No nodes had power 1',
        ]);
    }
}
