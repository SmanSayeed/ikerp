<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public function toArray($request)
    {
        return [
            'client_id' => $this->client_id,
            'client_name' => $this->client_name,
            'client_email' => $this->client_email,
            'client_phone' => $this->client_phone,
            'client_address' => $this->client_address,
            'client_is_vip' => $this->client_is_vip,
            'client_vip_discount' => $this->client_vip_discount,
            'date_range' => $this->date_range,
            'invoice_status' => $this->invoice_status,
            'device_usage_details' => json_decode($this->device_usage_details), // Decoding JSON to array
            'total_cost' => $this->total_cost,
            'original_cost' => $this->original_cost,
            'discount'=>$this->discount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'id' => $this->id,
            'due_date'=>$this->due_date,
            'seller_id'=>$this->seller_id,
            'invoice_generated_by_user_type'=>$this->invoice_generated_by_user_type,
            'invoice_generated_by_id'=>$this->parent_client_remotik_id,
            'for_child_client_remotik_id'=>$this->child_client_remotik_id

        ];
    }
}
