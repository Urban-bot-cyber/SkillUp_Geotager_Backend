<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="GuessCreateRequest",
 *     type="object",
 *     required={"latitude", "longitude", "error_distance", "user_id", "location_id"},
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
 *     ),
 *     @OA\Property(
 *         property="error_distance",
 *         type="number",
 *         format="float",
 *         description="Error distance between guess and actual location"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID of the user making the guess"
 *     ),
 *     @OA\Property(
 *         property="location_id",
 *         type="integer",
 *         description="ID of the location being guessed"
 *     ),
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
             'latitude' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{5})$/'],
             'longitude' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{5})$/'],
             'error_distance' => 'required|numeric',
             'user_id' => 'required|integer|exists:users,id',
             'location_id' => 'required|integer|exists:locations,id',
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