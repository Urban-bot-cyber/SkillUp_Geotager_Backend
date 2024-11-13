<?php

namespace App\Http\Requests;

class UpdateUserRequest extends ApiFormRequest
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
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100|unique:users,email,' . $this->user()->id, // Ensure unique email excluding current user
            'password' => [
                'nullable',
                'string',
                'min:8', // Minimum 8 characters
                'regex:/[a-z]/', // At least one lowercase letter
                'regex:/[A-Z]/', // At least one uppercase letter
                'regex:/[0-9]/', // At least one digit
                'regex:/[@$!%*?&#]/', // At least one special character
                'confirmed' // Ensures password confirmation matches
            ],
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048' // Optional profile picture upload
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
            'first_name.string' => 'First name must be a valid string.',
            'last_name.string' => 'Last name must be a valid string.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.confirmed' => 'The password confirmation does not match.',
            'profile_picture.image' => 'The profile picture must be an image file.',
            'profile_picture.mimes' => 'The profile picture must be a file of type: jpg, jpeg, png, or gif.',
            'profile_picture.max' => 'The profile picture may not be greater than 2MB.'
        ];
    }
}
