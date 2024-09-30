<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
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
        // Get 'from' and 'to' dates and client_id from the request
        $from = $request->input('from');
        $to = $request->input('to');
        $client_id = $request->input('client_id');

        // Fetch invoice data from the service
        $invoiceData = $this->invoiceService->getInvoiceData($from, $to, $client_id);

        // dd($invoiceData);
        // Check if data exists to prevent saving empty data
        if (empty($invoiceData['data'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'No usage data found for this client in the given date range.',
            ], 400);
        }
        // dd($invoiceData);
        // Create a new invoice
        $invoice = Invoice::create([
            'client_id' => $invoiceData['client']->id,
            'client_name' => $invoiceData['client']->name,
            'client_email' => $invoiceData['client']->email,
            'client_phone' => $invoiceData['client']->phone,
            'client_address' => $invoiceData['client']->address,
            'client_is_vip' => $invoiceData['client']->is_vip,
            'client_vip_discount' => $invoiceData['client']->vip_discount,
            'date_range' => $from . ' to ' . $to,
            'invoice_status' => 'unpaid', // Assuming default status is unpaid
            'device_usage_details' => json_encode($invoiceData['data']), // Store device details as JSON
            'original_cost'=>$invoiceData['originalInvoiceCost'],
            'total_cost' => $invoiceData['totalInvoiceCost'],
            'discount'=>$invoiceData['discount'],
        ]);

        // Return the response using InvoiceResource
        return response()->json([
            'status' => 'success',
            'message' => 'Invoice generated and saved successfully',
            'data' => new InvoiceResource($invoice),
        ]);
    }


    public function downloadInvoice(Request $request)
    {
        // Validate the request to ensure invoice_id is provided
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        // Fetch the invoice using the invoice_id from the request
        $invoice = Invoice::with('client')->findOrFail($request->input('invoice_id'));

        // Get the client details and device usage details
        $client = [
            'name' => $invoice->client_name,
            'address' => $invoice->client_address,
            'vip_discount' => $invoice->client_vip_discount,
        ];

        // Ensure device usage details is properly decoded into an array
        $deviceUsageDetails = json_decode($invoice->device_usage_details, true);

        // Calculate original and discounted total
        $originalInvoiceCost = $invoice->original_cost;
        $totalInvoiceCost = $invoice->total_cost;
        $discount = $invoice->discount;
        $vip_discount = $invoice->client_vip_discount;

        // Pass the invoice data to the Blade view
        $pdf = Pdf::loadView('pdf.invoice', [
            'client' => $client,
            'data' => $deviceUsageDetails,
            'originalInvoiceCost' => $originalInvoiceCost,
            'totalInvoiceCost' => $totalInvoiceCost,
            'vip_discount'=>$vip_discount,
            'discount'=>$discount
        ]);

        // Generate a filename with the current date and time
        $timestamp = \Carbon\Carbon::now()->format('Y-m-d_H-i-s');
        $fileName = "invoice_{$invoice->id}_{$timestamp}.pdf";

        // Return the PDF as a download
        return $pdf->download($fileName);
    }




    // public function downloadInvoice(Request $request)
    // {
    //     try {
    //         // Get 'from' and 'to' dates from the request
    //         $from = $request->input('from');
    //         $to = $request->input('to');
    //         $client_id = $request->input('client_id');
    //         // Fetch invoice data from the service
    //         $invoiceData = $this->invoiceService->getInvoiceData($from, $to,$client_id);

    //         // Generate the PDF
    //         $pdf = Pdf::loadView('pdf.invoice', $invoiceData);

    //         // Generate a filename with the current date and time
    //         $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
    //         $fileName = "invoice_{$timestamp}.pdf";

    //         // Download the generated PDF
    //         return $pdf->download($fileName);
    //     } catch (\Exception $e) {
    //         // Handle any errors and return a standardized error response
    //         return ResponseHelper::error('An error occurred while generating the invoice.', 500, ['error' => $e->getMessage()]);
    //     }

    // }

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
