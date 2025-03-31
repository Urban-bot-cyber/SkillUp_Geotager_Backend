<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="GuessCreateRequest",
 *     type="object",
 *     required={"latitude", "longitude"},
 *     @OA\Property(
 *         property="latitude",
 *         type="number",
 *         format="float",
 *         description="Guess latitude with exactly 5 decimal points",
 *         example=37.77493
 *     ),
 *     @OA\Property(
 *         property="longitude",
 *         type="number",
 *         format="float",
 *         description="Guess longitude with exactly 5 decimal points",
 *         example=-122.41942
 *     )
 * )
 */
class GuessCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Modify this according to your authorization logic
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
{
    return [
        'latitude' => 'required|numeric', 
        'longitude' => 'required|numeric',
    ];
}

     /**
      * Get custom error messages for validator errors.
      *
      * @return array
      */
     public function messages()
     {
         return [
             'latitude.regex' => 'The latitude must be a decimal number with exactly 5 decimal places.',
             'longitude.regex' => 'The longitude must be a decimal number with exactly 5 decimal places.',
         ];
     }
} 