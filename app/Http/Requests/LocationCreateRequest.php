<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationCreateRequest extends ApiFormRequest 
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Allow request authorization
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'latitude' => 'required|numeric', 
            'longitude' => 'required|numeric',
            'user_id' => 'required|integer', 
        ];
    }
}
