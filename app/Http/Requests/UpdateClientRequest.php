<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or implement authorization logic
    }

    public function rules(): array
    {
        $clientId = auth()->user()->id;
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:clients,email,' . $clientId,
            'address' => 'sometimes|string|nullable',
            'phone' => 'sometimes|string|nullable',
            'payment_due_date' => 'sometimes|date|nullable',
            'vat_slab' => 'sometimes|numeric|nullable',
            'gbs_information' => 'sometimes|string|nullable',
            'is_vip' => 'sometimes|boolean',
            'vip_discount' => 'sometimes|numeric|nullable',
        ];
    }
}
