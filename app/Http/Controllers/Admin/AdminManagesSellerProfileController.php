<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpdateSellerRequest;
use App\Http\Resources\AdminManagesClientResource;
use App\Models\Seller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\ResponseHelper; // Ensure you have a ResponseHelper class or adjust accordingly

class AdminManagesSellerProfileController extends Controller
{
    /**
     * Show the seller profile for a specific client.
     *
     * @param  int  $clientId
     * @return JsonResponse
     */
    public function show(int $clientId): JsonResponse
    {
        try {
            $client = Client::with('seller')->findOrFail($clientId);
            return ResponseHelper::success(new AdminManagesClientResource($client));
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the seller profile for a specific client.
     *
     * @param  AdminUpdateSellerRequest  $request
     * @param  int  $clientId
     * @return JsonResponse
     */
    public function update(AdminUpdateSellerRequest $request, int $clientId): JsonResponse
    {
        try {
            $clientId = (int) $clientId;
            $client = Client::with('seller')->findOrFail($clientId);

            // If the client does not have a seller profile, create one
            $seller = $client->seller ?? new Seller();
            $seller->fill($request->validated());
            $seller->client_id = $clientId;
            $seller->save();

            // Return success response
            return ResponseHelper::success(new AdminManagesClientResource($client), 'Seller profile updated successfully!');
        } catch (\Exception $e) {
            // Return error response
            return ResponseHelper::error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
