<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class RegisterClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients,email',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'client_remotik_id' => 'nullable|string',
            'payment_due_date' => 'nullable|date',
            'vat_slab' => 'nullable|numeric|min:0',
            'gbs_information' => 'nullable|string',
            'is_vip' => 'boolean',
            'is_seller' => 'boolean',
            'vip_discount' => 'nullable|numeric|min:0',
            'parent_client_id' => 'nullable|exists:clients,id',
            'status' => 'nullable|boolean',
            'is_child' => 'boolean',
            'is_parent'=>'boolean',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();

        throw new HttpResponseException(
            ResponseHelper::error('Validation failed', 422, $errors)
        );
    }
}
