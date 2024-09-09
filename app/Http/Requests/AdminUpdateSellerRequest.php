<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateSellerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Modify this if you want to add authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {  $clientId = $this->route('clientId');
        return [
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string|max:255',
            'company_logo' => 'nullable|string|max:255',
            'company_vat_number' => 'nullable',
            'company_kvk_number' => 'nullable',
            'status' => 'sometimes|boolean',
        ];
    }
}
