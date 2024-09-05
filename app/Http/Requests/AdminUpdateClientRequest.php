<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or implement authorization logic
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|nullable',
            'phone' => 'sometimes|string|nullable',
            'password' => 'sometimes|string|min:8|nullable',
            'is_seller' => 'sometimes|boolean',
            'payment_due_date' => 'sometimes|date|nullable',
            'vat_slab' => 'sometimes|numeric|nullable',
            'gbs_information' => 'sometimes|string|nullable',
            'is_vip' => 'sometimes|boolean',
            'vip_discount' => 'sometimes|numeric|nullable',
            'status' => 'sometimes|boolean',
            'email_verified_at' => 'sometimes|boolean|nullable',
        ];
    }
}
