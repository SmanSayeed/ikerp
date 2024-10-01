<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class SellerService
{
    /**
     * Register a client as a seller.
     *
     * @param array $data
     * @param int $clientId
     * @return Seller
     * @throws Exception
     */
    public function registerSeller(array $data, int $clientId): Seller
    {

            $client = Client::findOrFail($clientId);
            // Check if client is already a seller
            if ($client->is_seller) {
                throw new Exception('Client is already a seller.');
            }
            $data['status']=false;
            $seller = Seller::create(array_merge($data, ['client_id' => $clientId]));
            // Update client's is_seller status
            $client->update(['is_seller' => true]);
            return $seller;

    }

    /**
     * Retrieve seller information.
     *
     * @param int $clientId
     * @return Seller
     * @throws Exception
     */
    public function getSellerInfo(int $clientId): Seller
    {
        try {
            $seller = Seller::where('client_id', $clientId)->first();

            if (!$seller) {
                throw new ModelNotFoundException('Seller information not found.');
            }

            return $seller;
        } catch (ModelNotFoundException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception('An error occurred while retrieving the seller information.');
        }
    }

    /**
     * Update seller information.
     *
     * @param array $data
     * @param int $clientId
     * @return Seller
     * @throws Exception
     */
    public function updateSellerInfo(array $data, int $clientId): Seller
    {
        try {
            $seller = Seller::where('client_id', $clientId)->firstOrFail();

            $seller->update($data);

            return $seller;
        } catch (ModelNotFoundException $e) {
            throw new Exception('Seller information not found.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while updating the seller information.');
        }
    }
}
