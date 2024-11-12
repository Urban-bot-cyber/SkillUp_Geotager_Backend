<?php

namespace App\Http\Requests;

class RegisterRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:users',
            'password' => [
                'required',
                'string',
                'min:8', // Minimum 8 characters
                'regex:/[a-z]/', // At least one lowercase letter
                'regex:/[A-Z]/', // At least one uppercase letter
                'regex:/[0-9]/', // At least one digit
                'regex:/[@$!%*?&#]/', // At least one special character
                'confirmed' // Ensures password confirmation matches
            ]
        ];
    }

    /**
     * Get custom error messages for the defined validation rules.
     *
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.confirmed' => 'The password confirmation does not match.'
        ];
    }
}
