<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'client_remotik_id'=>$this->client_remotik_id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone,
            'is_vip' => $this->is_vip,
            'vat_slab' => $this->vat_slab,
            'gbs_information' => $this->gbs_information,
            'parent_client_id' => $this->parent_client_id,
            'status' => $this->status,
            'vip_discount' => $this->vip_discount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_seller' => $this->is_seller,
            'seller' => $this->when($this->is_seller, new SellerResource($this->seller)),
            'is_child'=>$this->is_child,
            'is_parent'=>$this->is_parent
        ];
    }
}
