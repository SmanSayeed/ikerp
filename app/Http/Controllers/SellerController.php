<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    /**
     * Make a client become a seller.
     *
     * @param Request $request
     * @param int $clientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function becomeSeller(Request $request, $clientId)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string',
            'company_address' => 'required|string',
            'company_logo' => 'nullable|string',
            'company_vat_number' => 'required|string|unique:sellers,company_vat_number',
            'company_kvk_number' => 'required|string|unique:sellers,company_kvk_number',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $client = Client::findOrFail($clientId);

        $seller = Seller::create([
            'company_name' => $request->company_name,
            'company_address' => $request->company_address,
            'company_logo' => $request->company_logo,
            'company_vat_number' => $request->company_vat_number,
            'company_kvk_number' => $request->company_kvk_number,
            'client_id' => $clientId,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Client successfully registered as a seller.',
            'data' => $seller
        ], 201);
    }

    /**
     * Retrieve seller information for a client.
     *
     * @param int $clientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSellerInfo($clientId)
    {
        $seller = Seller::where('client_id', $clientId)->first();

        if (!$seller) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seller information not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $seller
        ], 200);
    }

    /**
     * Update seller information.
     *
     * @param Request $request
     * @param int $clientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSellerInfo(Request $request, $clientId)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'sometimes|required|string',
            'company_address' => 'sometimes|required|string',
            'company_logo' => 'nullable|string',
            'company_vat_number' => 'sometimes|required|string',
            'company_kvk_number' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $seller = Seller::where('client_id', $clientId)->firstOrFail();

        $seller->update($request->only([
            'company_name',
            'company_address',
            'company_logo',
            'company_vat_number',
            'company_kvk_number',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Seller information updated successfully.',
            'data' => $seller
        ], 200);
    }
}
