<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientUpdateSellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or implement authorization logic
    }

    public function rules(): array
    {
        return [
            'company_name' => 'sometimes|required|string',
            'company_address' => 'sometimes|required|string',
            'company_logo' => 'nullable|string',
            'company_vat_number' => 'sometimes|required|string|unique:sellers,company_vat_number,' . $this->route('clientId'),
            'company_kvk_number' => 'sometimes|required|string|unique:sellers,company_kvk_number,' . $this->route('clientId'),
            'company_iban_number'=>'nullable|unique:sellers,company_iban_number',
        ];
    }
}
