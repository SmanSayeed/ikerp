<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\PowerData;
use App\Models\Seller;
use App\Services\InvoiceChildClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Exception;

class InvoiceChildClientController extends Controller
{

    protected $invoiceService;

    public function __construct(InvoiceChildClientService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function generateChildClientInvoice(Request $request)
    {
        // Validate the request to ensure 'from', 'to', and 'client_id' are provided
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'due_date' => 'nullable|date|after_or_equal:from',
            'parent_client_remotik_id' => 'required|exists:clients,client_remotik_id',
            'child_client_remotik_id'=>'required|exists:clients,client_remotik_id'
        ]);

        // Get 'from', 'to' dates, and client_id from the request
        $from = $request->input('from');

        $to = $request->input('to');

        $parent_client_remotik_id = $request->input('parent_client_remotik_id');

        $due_date = $request->input('due_date');

        $child_client_remotik_id = $request->input('child_client_remotik_id');

        $powerClient = PowerData::where('client_remotik_id', $parent_client_remotik_id)->first();

        if(!$powerClient) {
            return ResponseHelper::error('Parent Client not found', 400);
        }

        $powerChildClient = PowerData::where('child_client_remotik_id', $child_client_remotik_id)->first();

        if(!$powerChildClient) {
            return ResponseHelper::error('Child Client not found', 400);
        }

        try {
            // Check if an invoice already exists for the given client and date range
            $existingInvoice = Invoice::where('client_remotik_id', $child_client_remotik_id)
                ->where('date_range', $from . ' to ' . $to)
                ->first();

            if ($existingInvoice) {
                return ResponseHelper::error('An invoice for this client already exists for the specified date range.', 400);
            }

            // Fetch invoice data from the service
            $invoiceData = $this->invoiceService->getChildClientInvoiceData($from, $to, $child_client_remotik_id,$due_date);


            if($invoiceData == 'email-not-found'){
                return ResponseHelper::error('Client email not found', 400);
            }

            if(!$invoiceData){
                return ResponseHelper::error('No Client found', 400);
            }
            // Check if data exists to prevent saving empty data
            if (empty($invoiceData['data'])) {
                return ResponseHelper::error('No usage data found for this client in the given date range.', 400);
            }

            $seller = Seller::where('client_remotik_id',$parent_client_remotik_id)->firstOrFail();

            $user = auth()->user();


            // Create a new invoice
            $invoice = Invoice::create([
                'client_id' => $invoiceData['client']->id,
                'client_remotik_id' => $invoiceData['client']->client_remotik_id,
                'client_name' => $invoiceData['client']->name,
                'client_email' => $invoiceData['client']->email,
                'client_phone' => $invoiceData['client']->phone,
                'client_address' => $invoiceData['client']->address,
                'client_is_vip' => $invoiceData['client']->is_vip,
                'client_vip_discount' => $invoiceData['client']->vip_discount,
                'date_range' => $from . ' to ' . $to,
                'invoice_status' => 'unpaid', // Assuming default status is unpaid
                'device_usage_details' => json_encode($invoiceData['data']), // Store device details as JSON
                'original_cost' => $invoiceData['originalInvoiceCost'],
                'total_cost' => $invoiceData['totalInvoiceCost'],
                'discount' => $invoiceData['discount'],
                'due_date' => $invoiceData['due_date'],
                'seller_id' => $seller->id,
                'invoice_generated_by_user_type'=>'client',
                'invoice_generated_by_id'=>$user->client_remotik_id,
                'invoice_generated_by_name'=>$user->client_remotik_id,
                'for_child_client_remotik_id'=>$child_client_remotik_id
            ]);
            // Return the response using ResponseHelper
            return ResponseHelper::success(new InvoiceResource($invoice), 'Invoice generated and saved successfully');
        } catch (\Exception $e) {
            // Log the exception for debugging
            \Log::error('Invoice generation failed: ' . $e);

            // Return an error response
            return ResponseHelper::error('An error occurred while generating the invoice. Please try again later.', 500);
        }
    }



    public function updateInvoice(Request $request, $invoice_id)
    {
        // Validate incoming request data
        $request->validate([
            'client_name' => 'nullable|string',
            'client_email' => 'nullable|email',
            'client_phone' => 'nullable|string',
            'client_address' => 'nullable|string',
            'invoice_status' => 'nullable|in:paid,unpaid,cancelled',
            'total_cost' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'due_date' => 'nullable|date',
        ]);

        try {
            // Find the invoice by its ID or fail
            $invoice = Invoice::findOrFail($invoice_id);

            // Update the invoice details
            $invoice->update($request->only([
                'client_name', 'client_email', 'client_phone',
                'client_address', 'invoice_status', 'total_cost',
                'discount', 'due_date'
            ]));

            // Return success response with updated invoice
            return ResponseHelper::success(new InvoiceResource($invoice), 'Invoice updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Log specific exception for debugging
            \Log::error("Invoice not found: " . $e->getMessage());

            // Return error response using ResponseHelper for model not found
            return ResponseHelper::error('Invoice not found.', 404);

        } catch (\Exception $e) {
            // Log the general exception for debugging
            \Log::error("Error updating invoice: " . $e->getMessage());

            // Return error response using ResponseHelper for any other errors
            return ResponseHelper::error('Failed to update invoice. Please try again later.', 500);
        }
    }

    public function deleteInvoice($invoice_id)
    {
        try {
            // Find the invoice by its ID or fail
            $invoice = Invoice::findOrFail($invoice_id);

            // Delete the invoice
            $invoice->delete();

            // Return success response
            return ResponseHelper::success([], 'Invoice deleted successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Log specific exception for debugging
            \Log::error("Invoice not found: " . $e->getMessage());

            // Return error response using ResponseHelper for model not found
            return ResponseHelper::error('Invoice not found.', 404);

        } catch (\Exception $e) {
            // Log the general exception for debugging
            \Log::error("Error deleting invoice: " . $e->getMessage());

            // Return error response using ResponseHelper for any other errors
            return ResponseHelper::error('Failed to delete invoice. Please try again later.', 500);
        }
    }


    public function viewInvoice($invoice_id)
    {
        try {
            // Fetch the invoice using the invoice_id from the request
            $invoice = Invoice::with('client')->findOrFail($invoice_id);

            if (!$invoice) {
                throw new \Exception('Invoice not found');
            }
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
            $invoiceData =  [
                'client' => $client,
                'data' => $deviceUsageDetails,
                'originalInvoiceCost' => $originalInvoiceCost,
                'totalInvoiceCost' => $totalInvoiceCost,
                'vip_discount' => $vip_discount,
                'discount' => $discount,
                'invoice_id' => $invoice->id,
                'invoice_date' => $invoice->created_at,
                'due_date' => $invoice->due_date,
                'seller_id'=>$invoice->seller_id,
                'invoice_generated_by_user_type'=>$invoice->invoice_generated_by_user_type,
                'invoice_generated_by_id'=>$invoice->parent_client_remotik_id,
                'for_child_client_remotik_id'=>$invoice->child_client_remotik_id
            ];

            return ResponseHelper::success($invoiceData, 'Invoice data retrieved.', 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Log specific exception for debugging
            \Log::error("Invoice not found: " . $e->getMessage());

            // Return error response using ResponseHelper for model not found
            return ResponseHelper::error('Invoice not found.', 404);

        } catch (\Exception $e) {
            // Log the general exception for debugging
            \Log::error("Error generating invoice PDF: " . $e->getMessage());

            // Return error response using ResponseHelper for any other errors
            return ResponseHelper::error('Failed to generate invoice. Please try again later.', 500);
        }

    }
    public function previewInvoice($invoice_id)
    {
        try {
            // Use the service to fetch the invoice data
            $invoiceData = $this->invoiceService->getPdfInvoiceData($invoice_id);

            // Generate the PDF
            $pdf = $this->invoiceService->generateInvoicePdf($invoiceData);

            // Return the generated PDF as a response
            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline');

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to generate PDF'], 500);
        }
    }

    public function downloadInvoice($invoice_id)
    {
        try {
            // Use the service to fetch the invoice data
            $invoiceData = $this->invoiceService->getPdfInvoiceData($invoice_id);

            // Generate the PDF
            $pdf = $this->invoiceService->generateInvoicePdf($invoiceData);

            // Generate a filename with the current date and time
            $timestamp = \Carbon\Carbon::now()->format('Y-m-d_H-i-s');
            $fileName = "invoice_{$invoice_id}_{$timestamp}.pdf";

            // Return the PDF as a download
            return $pdf->download($fileName);

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to download invoice'], 500);
        }
    }

    public function getChildClientInvoices(Request $request,$client_remotik_id)
    {


        try {
            // Start building the query
            $query = Invoice::query();
            $query->where('invoice_generated_by_user_type','client')->where('invoice_generated_by_id',$client_remotik_id);

            $perPage = 100;
            if ($request->filled('perPage')) {
                $perPage = $request->input('perPage');
            }
            // Apply filters based on request parameters
            if ($request->filled('invoice_id')) {
                $query->where('id', $request->input('invoice_id'));
            }

            if ($request->filled('created_date')) {
                $query->whereDate('created_at', $request->input('created_date'));
            }

            if ($request->filled('client_id')) {
                $query->where('client_id', $request->input('client_id'));
            }

            if ($request->filled('invoice_status')) {
                $query->where('invoice_status', $request->input('invoice_status'));
            }

            // Common search filter
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->where('client_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('client_email', 'like', '%' . $searchTerm . '%')
                        ->orWhere('client_phone', 'like', '%' . $searchTerm . '%')
                        ->orWhere('date_range', 'like', '%' . $searchTerm . '%');
                });
            }

            // Get the invoices in descending order, with pagination
            $invoices = $query->orderBy('created_at', 'desc')->paginate($perPage); // Change 10 to your desired page size

            // Return the success response using ResponseHelper
            return ResponseHelper::success($invoices);

        } catch (\Exception $e) {
            // Log the error message for debugging purposes
            \Log::error('Error fetching invoices: ' . $e->getMessage());

            // Return the error response using ResponseHelper
            return ResponseHelper::error('Failed to fetch invoices. Please try again later.', 500);
        }
    }










}
