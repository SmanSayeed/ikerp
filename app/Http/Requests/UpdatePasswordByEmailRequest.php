<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class UpdatePasswordByEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', function ($attribute, $value, $fail) {
                // Custom validation logic to check if the token is valid and not used
                $resetToken = DB::table('password_reset_tokens')
                    ->where('token', $value)
                    ->where('email', $this->input('email')) // Match the email as well
                    ->where('is_valid', true) // Check if the token is still valid
                    ->first();

                if (!$resetToken) {
                    $fail('The provided token is invalid or has already been used.');
                }

                // Check if the token has expired
                if ($resetToken && \Carbon\Carbon::parse($resetToken->created_at)->addMinutes(60)->isPast()) {
                    $fail('The provided token has expired.');
                }
            }],
            'email' => 'required|email|exists:users,email', // Check if the email exists in the users table
            'password' => 'required|string|confirmed|min:8',
        ];
    }

    public function messages()
    {
        return [
            'email.exists' => 'The provided email does not exist in our records.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 8 characters long.',
        ];
    }
}
