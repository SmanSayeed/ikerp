<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientBecomesSellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or implement authorization logic
    }

    public function rules(): array
    {
        return [
            'company_name' => 'required|string',
            'company_address' => 'required|string',
            'company_logo' => 'nullable|string',
            'company_vat_number' => 'required|string|unique:sellers,company_vat_number',
            'company_kvk_number' => 'required|string|unique:sellers,company_kvk_number',
        ];
    }
}
