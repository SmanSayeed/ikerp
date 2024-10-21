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

class InvoiceService
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
    public function getInvoiceData($from = null, $to = null, $client_remotik_id,$due_date=null)
    {
        // Fetch client information
        $clientData = Client::where('client_remotik_id', $client_remotik_id)->first();

        if(!$clientData->email){
            return 'email-not-found';
        }

        if (!$clientData) {
            // Return an empty response or handle it accordingly if client is not found
            return ['data' => [], 'totalInvoiceCost' => 0, 'originalInvoiceCost' => 0, 'client' => null];
        }

        // Query to fetch PowerData for the specific client
        $query = PowerData::select(DB::raw('client_remotik_id, nodeid, node_name, COUNT(DISTINCT DATE(time)) as days_active'))
            ->where('nodeid', '!=', '*')
            ->where('power', '=', 1)
            ->where('client_remotik_id', $clientData->client_remotik_id)
            ->groupBy( 'client_remotik_id','nodeid', 'node_name');

        // Apply date filters
        if ($from && $to) {
            $query->whereDate('time', '>=', $from)
                  ->whereDate('time', '<=', $to);
        } elseif ($from) {
            $query->whereDate('time', '=', $from);
        }

        $invoiceData = $this->generateInvoiceWithCalculation($query,$due_date,$clientData,$from,$to);

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
            $seller = [];
            if($invoice->invoice_generated_by_user_type=="admin"){
                $seller = [
                    'company_name' => 'DVRS',
                    'company_address' => 'DVRS Address',
                    'company_vat_number'=>'3215',
                    'company_kvk_number'=>
                    '4567',
                    'company_iban_number'=>'1234'
                ];

            }else{

                $seller = $invoice->seller;
            }

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
                'vat_slab_amount'=>$invoice->vat_slab_amount,
                'client_vat_slab'=>$invoice->client_vat_slab,
                'seller'=>$seller
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
