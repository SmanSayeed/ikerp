<?php

namespace App\Traits;

trait InvoiceTrait
{
    /**
     * Generate invoice with calculations.
     */
    public function generateInvoiceWithCalculation($query, $due_date = null, $clientData, $from, $to)
    {
        // Fetch the data and map it to the desired structure
        $data = $this->invoicePowerCostMap($query);
        \Log::info("------------VIP-------------- ".$clientData->vip_discount);
        $discountPercentage = (float)$clientData->vip_discount;
        $vat_slab = (float) $clientData->vat_slab;

        // Calculate total costs
        $originalInvoiceCost = $data->sum('original_total');
        $discount = 0;
        $vat_slab_amount = 0;
        $totalInvoiceCost = $originalInvoiceCost;

        // Apply discount if available
        if ($discountPercentage) {
            $totalInvoiceCost = $this->calculateDiscount((float)$originalInvoiceCost, (float)$discountPercentage);
            $discount = $this->getDiscountPercentageAmount((float)$originalInvoiceCost, (float)$discountPercentage);
        }

        // Apply VAT if applicable
        if ($vat_slab) {
            $vat_slab_amount = $this->getVatSlabAmount((float)$totalInvoiceCost, (float)$vat_slab);
            $totalInvoiceCost = $this->calculateVatSlab((float)$totalInvoiceCost, (float)$vat_slab);
        }

        // Return structured data
        return [
            'data' => $data,
            'originalInvoiceCost' => $originalInvoiceCost,
            'discount' => $discount,
            'totalInvoiceCost' => $totalInvoiceCost,
            'discountPercentage' => $discountPercentage,
            'from' => $from,
            'to' => $to,
            'client' => $clientData,
            'due_date' => $due_date,
            'client_vat_slab' => $clientData->vat_slab,
            'vat_slab_amount' => $vat_slab_amount,
        ];
    }

    /**
     * Map invoice power cost.
     */
    public function invoicePowerCostMap($query)
    {
        return $query->get()->map(function ($item) {
            // Calculate totals
            $originalTotal = $item->days_active * $this->pricePerNode;

            return [
                'node_name' => $item->node_name,
                'days_active' => $item->days_active,
                'price_per_day' => $this->pricePerNode,
                'original_total' => $originalTotal,
            ];
        });
    }

    /**
     * Calculate the discount amount based on percentage.
     */
    public function getDiscountPercentageAmount($total, $discount)
    {
        return ($total * $discount) / 100;
    }

    /**
     * Calculate the VAT slab amount based on percentage.
     */
    public function getVatSlabAmount($total, $vat_slab)
    {
        return ($total * $vat_slab) / 100;
    }

    /**
     * Calculate the total amount after applying the VAT slab.
     */
    public function calculateVatSlab($total, $vat_slab)
    {
        return $total + ($total * $vat_slab) / 100;
    }

    /**
     * Calculate the total amount after applying the discount.
     */
    public function calculateDiscount($total, $discount)
    {
        return $total - ($total * $discount) / 100;
    }
}
