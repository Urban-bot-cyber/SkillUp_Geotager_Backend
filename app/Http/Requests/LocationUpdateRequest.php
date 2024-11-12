<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationUpdateRequest extends ApiFormRequest 
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
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048', 
            'latitude' => 'sometimes|numeric', 
            'longitude' => 'sometimes|numeric', 
            'user_id' => 'sometimes|integer', 
        ];
    }
}
