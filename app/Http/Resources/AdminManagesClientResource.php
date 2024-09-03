<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminManagesClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone,
            'is_seller' => $this->is_seller,
            'payment_due_date' => $this->payment_due_date,
            'vat_slab' => $this->vat_slab,
            'gbs_information' => $this->gbs_information,
            'is_vip' => $this->is_vip,
            'vip_discount' => $this->vip_discount,
            'email_verified_at' => $this->email_verified_at,
            'status' => $this->status,
            'parent_client_id' => $this->parent_client_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
