<?php

namespace App\Services;

use App\Models\PowerData;
use App\Models\Client;
use App\Traits\InvoiceTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Exception;

class InvoiceChildClientService
{
    use InvoiceTrait;
    protected $pricePerNode = 100; // Example constant price

    /**
     * Generate invoice data.
     *
     * @param string|null $from
     * @param string|null $to
     * @return array
     */
    public function getChildClientInvoiceData($from = null, $to = null, $child_client_remotik_id,$due_date=null)
    {
        // Fetch client information
        $clientData = Client::where('client_remotik_id', $child_client_remotik_id)->first();

        if(!$clientData->email){
            return 'email-not-found';
        }

        if (!$clientData) {
            // Return an empty response or handle it accordingly if client is not found
            return ['data' => [], 'totalInvoiceCost' => 0, 'originalInvoiceCost' => 0, 'client' => null];
        }

        // Query to fetch PowerData for the specific client
        $query = PowerData::select(DB::raw('client_remotik_id,child_client_remotik_id, nodeid, node_name, COUNT(DISTINCT DATE(time)) as days_active'))
            ->where('nodeid', '!=', '*')
            ->where('power', '=', 1)
             ->where('child_client_remotik_id', $clientData->client_remotik_id)
            ->groupBy( 'client_remotik_id','child_client_remotik_id','nodeid', 'node_name');

        // Apply date filters
        if ($from && $to) {
            $query->whereDate('time', '>=', $from)
                  ->whereDate('time', '<=', $to);
        } elseif ($from) {
            $query->whereDate('time', '=', $from);
        }

        $invoiceData = $this->generateInvoiceWithCalculation($query, $due_date, $clientData, $from, $to);


        return $invoiceData;
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
            $seller = $invoice->seller;

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
                'seller_id'=>$invoice->seller_id,
                'invoice_generated_by_user_type'=>$invoice->invoice_generated_by_user_type,
                'invoice_generated_by_id'=>$invoice->parent_client_remotik_id,
                'for_child_client_remotik_id'=>$invoice->child_client_remotik_id,
                $seller=>$seller,
                'vat_slab_amount'=>$invoice->vat_slab_amount,
                'client_vat_slab'=>$invoice->client_vat_slab
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
