<?php

namespace App\Http\Controllers;

use App\Models\PowerData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function generateInvoice(Request $request)
    {
        // Get 'from' and 'to' dates from the request
        $from = $request->input('from');
        $to = $request->input('to');

        // Query builder to fetch unique client_id, nodeid, and date with power status aggregation
        $query = PowerData::select(DB::raw('client_id, nodeid, DATE(time) as date, MAX(power) as power_status'))
            ->where('nodeid', '!=', '*') // Skip entries where nodeid is "*"
            ->groupBy('client_id', 'nodeid', DB::raw('DATE(time)')); // Group by client_id, nodeid, and date

        // Apply the correct filters based on the presence of 'from' and 'to'
        if ($from && $to) {
            // If both 'from' and 'to' are provided, filter between those dates
            $query->whereDate('time', '>=', $from)
                  ->whereDate('time', '<=', $to);
        } elseif ($from) {
            // If only 'from' is provided, filter for that specific date only (== from)
            $query->whereDate('time', '=', $from);
        }

        // If neither 'from' nor 'to' is provided, the query will fetch all records

        // Execute the query and get the results
        $data = $query->get()->map(function ($item) {
            return [
                'client_id' => $item->client_id,
                'node_id' => $item->nodeid,
                'time' => $item->date,
                'power_status' => $item->power_status == 1 ? true : false,
            ];
        });

        return response()->json($data);
    }
}
