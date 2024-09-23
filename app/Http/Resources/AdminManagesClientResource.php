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
            'id' => $request->id,
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'phone' => $request->phone,
            'is_seller' => $request->is_seller,
            'payment_due_date' => $request->payment_due_date,
            'vat_slab' => $request->vat_slab,
            'gbs_information' => $request->gbs_information,
            'is_vip' => $request->is_vip,
            'vip_discount' => $request->vip_discount,
            'email_verified_at' => $request->email_verified_at,
            'status' => $request->status,
            'parent_client_id' => $request->parent_client_id,
            'client_remotik_id'=> $request->client_remotik_id,
            'created_at' => $request->created_at,
            'updated_at' => $request->updated_at,
            'seller' => $this->when($request->is_seller, new SellerResource($request->seller)),
        ];
    }
}
