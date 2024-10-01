<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\ClientBecomesSellerRequest;
use App\Http\Requests\ClientUpdateSellerRequest;
use App\Services\SellerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SellerResource;
use Exception;

class SellerController extends Controller
{
    protected $sellerService;

    public function __construct(SellerService $sellerService)
    {
        $this->sellerService = $sellerService;
    }

    public function becomeSeller(ClientBecomesSellerRequest $request, int $clientId): JsonResponse
    {
        try {
            $seller = $this->sellerService->registerSeller($request->validated(), $clientId);
            return ResponseHelper::success(new SellerResource($seller), 'Client successfully registered as a seller.', 201);
        } catch (\Exception $e) {

            return ResponseHelper::error('Failed to register seller.', 500, ['error' => $e->getMessage()]);
        }catch (ModelNotFoundException $e) {
            throw new Exception('Client not found.');
        } catch (Exception $e) {
            \Log::error('Failed to register seller: ' . $e);
            throw new Exception('An error occurred while registering the seller.');
        }
    }

    public function getSellerInfo(int $clientId): JsonResponse
    {
        try {
            $seller = $this->sellerService->getSellerInfo($clientId);
            return ResponseHelper::success(new SellerResource($seller), 'Seller information retrieved successfully.');
        } catch (Exception $e) {
            return ResponseHelper::error('Failed to retrieve seller information.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function updateSellerInfo(ClientUpdateSellerRequest $request, int $clientId): JsonResponse
    {
        try {
            $seller = $this->sellerService->updateSellerInfo($request->validated(), $clientId);
            return ResponseHelper::success(new SellerResource($seller), 'Seller information updated successfully.');
        } catch (\Exception $e) {
            return ResponseHelper::error('Failed to update seller information.', 500, ['error' => $e->getMessage()]);
        }
    }
}
