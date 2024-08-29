<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or implement authorization logic
    }

    public function rules(): array
    {
        return [
            'old_password'=>'required|string|min:8',
            'password' => 'required|string|confirmed|min:8',
        ];
    }
}
