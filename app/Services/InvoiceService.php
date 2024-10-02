<?php

namespace App\Services;

use App\Models\PowerData;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Exception;

class InvoiceService
{
    protected $pricePerNode = 100; // Example constant price

    /**
     * Generate invoice data.
     *
     * @param string|null $from
     * @param string|null $to
     * @return array
     */
    public function getInvoiceData($from = null, $to = null, $client_id,$due_date=null)
    {
        // Fetch client information
        $clientData = Client::find($client_id);

        if (!$clientData) {
            // Return an empty response or handle it accordingly if client is not found
            return ['data' => [], 'totalInvoiceCost' => 0, 'originalInvoiceCost' => 0, 'client' => null];
        }

        // Query to fetch PowerData for the specific client
        $query = PowerData::select(DB::raw('client_id, nodeid, node_name, COUNT(DISTINCT DATE(time)) as days_active'))
            ->where('nodeid', '!=', '*')
            ->where('power', '=', 1)
            ->where('client_id', $clientData->id) // Ensure it's filtering by client
            ->groupBy('client_id', 'nodeid', 'node_name');

        // Apply date filters
        if ($from && $to) {
            $query->whereDate('time', '>=', $from)
                  ->whereDate('time', '<=', $to);
        } elseif ($from) {
            $query->whereDate('time', '=', $from);
        }

        // Fetch the data and map it to the desired structure
        $data = $query->get()->map(function ($item) use ($clientData) {
            // Calculate totals
            $originalTotal = $item->days_active * $this->pricePerNode;

            return [
                'node_name' => $item->node_name,
                'days_active' => $item->days_active,
                'price_per_day' => $this->pricePerNode,
                'original_total' => $originalTotal,
            ];
        });

        $discountPercentage = (float) $clientData->vip_discount;

        // Calculate total costs
        $originalInvoiceCost = $data->sum('original_total');
        $discount = 0;
        $totalInvoiceCost = $originalInvoiceCost;

        if($discountPercentage) {

            $totalInvoiceCost = $originalInvoiceCost - $originalInvoiceCost * ($discountPercentage / 100);

            $discount = $originalInvoiceCost - $totalInvoiceCost;
        }



        // Return structured data
        return [
            'data' => $data,
            'originalInvoiceCost' => $originalInvoiceCost,
            'discount'=>$discount,
            'totalInvoiceCost' => $totalInvoiceCost,
            'discountPercentage' => $discountPercentage,
            'from' => $from,
            'to' => $to,
            'client' => $clientData,
            'due_date'=>$due_date
        ];
    }

    public function getPdfInvoiceData($invoice_id)
    {
        try {
            // Fetch the invoice with the client details
            $invoice = Invoice::with('client')->findOrFail($invoice_id);

            // Prepare client data
            $client = [
                'name' => $invoice->client_name,
                'address' => $invoice->client_address,
                'vip_discount' => $invoice->client_vip_discount,
            ];

            // Decode the device usage details from JSON to an array
            $deviceUsageDetails = json_decode($invoice->device_usage_details, true);

            // Prepare the necessary invoice data
            $invoiceData = [
                'client' => $client,
                'data' => $deviceUsageDetails,
                'originalInvoiceCost' => $invoice->original_cost,
                'totalInvoiceCost' => $invoice->total_cost,
                'vip_discount' => $invoice->client_vip_discount,
                'discount' => $invoice->discount,
                'invoice_id' => $invoice->id,
                'invoice_date' => $invoice->created_at,
                'due_date' => $invoice->due_date,
            ];

            return $invoiceData;
        } catch (Exception $e) {
            Log::error("Error fetching invoice: " . $e->getMessage());
            throw new Exception('Error fetching invoice.');
        }
    }


    public function generateInvoicePdf($invoiceData)
    {
        try {
            // Load the PDF view with the data
            $pdf = \Pdf::loadView('pdf.invoice', $invoiceData);

            return $pdf;
        } catch (Exception $e) {
            Log::error("Error generating PDF: " . $e->getMessage());
            throw new Exception('Error generating PDF.');
        }
    }

}
