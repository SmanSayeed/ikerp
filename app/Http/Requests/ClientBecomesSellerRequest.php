<?php

namespace App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use App\Models\Seller;


use Illuminate\Foundation\Http\FormRequest;

class ClientBecomesSellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or implement authorization logic
    }

    public function rules(): array
    {

        $clientId = Auth::id();
        return [
            'client_id' => [
                'required',
                'exists:clients,id', // Ensure client_id exists in clients table
                function ($attribute, $value, $fail) use ($clientId) {
                    // Check if the client_id matches the authenticated user's ID
                    if ($value != $clientId) {
                        $fail('Invalid client ID.');
                    }
                },
                function ($attribute, $value, $fail) {
                    // Check if the client_id already exists in the sellers table
                    if (Seller::where('client_id', $value)->exists()) {
                        $fail('You are already registered as a seller.');
                    }
                },
            ],

        ];
    }

    public function messages(): array
    {
        return [
            'company_vat_number.unique' => 'The VAT number has already been taken.',
            'company_kvk_number.unique' => 'The KVK number has already been taken.',
       ];
    }
}
