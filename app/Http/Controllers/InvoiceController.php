<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\PowerData;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class InvoiceController extends Controller
{

    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }
    public function generateInvoice(Request $request)
    {
         // Get 'from' and 'to' dates from the request
         $from = $request->input('from');
         $to = $request->input('to');

          // Fetch invoice data from the service
          $invoiceData = $this->invoiceService->getInvoiceData($from, $to);

         // Download the generated PDF
         return response()->json(  $invoiceData);

    }


    public function downloadInvoice(Request $request)
    {
        try {
            // Get 'from' and 'to' dates from the request
            $from = $request->input('from');
            $to = $request->input('to');

            // Fetch invoice data from the service
            $invoiceData = $this->invoiceService->getInvoiceData($from, $to);

            // Generate the PDF
            $pdf = Pdf::loadView('pdf.invoice', [
                'data' => $invoiceData['data'],
                'totalInvoiceCost' => $invoiceData['totalInvoiceCost']
            ]);

            // Generate a filename with the current date and time
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $fileName = "invoice_{$timestamp}.pdf";

            // Download the generated PDF
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            // Handle any errors and return a standardized error response
            return ResponseHelper::error('An error occurred while generating the invoice.', 500, ['error' => $e->getMessage()]);
        }

    }

    public function filterPowerUsage(Request $request)
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
                'nodeid' => $item->nodeid,
                'node_name' => $item->node_name,
                'time' => $item->date,
                'power_status' => $item->power_status == 1 ? true : false,
            ];
        });

        return response()->json($data);
    }




    // public function downloadInvoice(Request $request)
    // {
    //     // Get 'from' and 'to' dates from the request
    //     $from = $request->input('from');
    //     $to = $request->input('to');

    //     // Define a constant price for each node
    //     $pricePerNode = 100; // Example constant price

    //     // Query to fetch nodes with power 1
    //     $query = PowerData::select(DB::raw('client_id, nodeid,node_name, DATE(time) as date, MAX(power) as power_status'))
    //         ->where('nodeid', '!=', '*')
    //         ->where('power', '=', 1) // Only where power is 1
    //         ->groupBy('client_id', 'nodeid','node_name', DB::raw('DATE(time)'));

    //     // Apply date filters
    //     if ($from && $to) {
    //         $query->whereDate('time', '>=', $from)
    //               ->whereDate('time', '<=', $to);
    //     } elseif ($from) {
    //         $query->whereDate('time', '=', $from);
    //     }

    //     // Get the data
    //     $data = $query->get()->map(function ($item) {
    //         return [
    //             'client_id' => $item->client_id,
    //             'node_name' => $item->node_name,
    //             'date' => $item->date,
    //         ];
    //     });

    //     // Calculate total price
    //     $totalPrice = count($data) * $pricePerNode;

    //     // Pass data to the view and generate the PDF
    //     $pdf = PDF::loadView('pdf.invoice', [
    //         'data' => $data,
    //         'pricePerNode' => $pricePerNode,
    //         'totalPrice' => $totalPrice
    //     ]);

    //     // Download the generated PDF
    //     return $pdf->download('invoice.pdf');
    // }
}
