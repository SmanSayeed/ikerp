<?php

namespace App\Services;

use App\Models\PowerData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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
    public function getInvoiceData($from = null, $to = null)
    {
        $query = PowerData::select(DB::raw('client_id, nodeid, node_name, COUNT(DISTINCT DATE(time)) as days_active'))
            ->where('nodeid', '!=', '*')
            ->where('power', '=', 1) // Only where power is 1
            ->groupBy('client_id', 'nodeid', 'node_name');

        // Apply date filters
        if ($from && $to) {
            $query->whereDate('time', '>=', $from)
                ->whereDate('time', '<=', $to);
        } elseif ($from) {
            $query->whereDate('time', '=', $from);
        }

        // Get the data
        $data = $query->get()->map(function ($item) {
            $total = $item->days_active * $this->pricePerNode; // Total = days active * unit price
            return [
                'node_name' => $item->node_name,
                'days_active' => $item->days_active,
                'price_per_day' => $this->pricePerNode,
                'total' => $total
            ];
        });

        // Calculate total invoice cost (sum of all node totals)
        $totalInvoiceCost = $data->sum('total');

        return [
            'data' => $data,
            'totalInvoiceCost' => $totalInvoiceCost
        ];
    }
}
