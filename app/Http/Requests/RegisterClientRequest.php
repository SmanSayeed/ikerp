<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,sub_admin,service_provider,client',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'client_type' => 'required|in:buyer,seller,both',
            'payment_due_date' => 'nullable|date',
            'vat_slab' => 'nullable|numeric|min:0',
            'gbs_information' => 'nullable|string',
            'is_vip' => 'boolean',
            'vip_discount' => 'nullable|numeric|min:0',
            'parent_client_id' => 'nullable|exists:clients,id',
        ];
    }

}
