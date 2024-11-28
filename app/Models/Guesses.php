<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Guess",
 *     type="object",
 *     required={"id", "latitude", "longitude", "error_distance", "user_id", "location_id"},
 *     @OA\Property(property="id", type="integer", description="Guess ID"),
 *     @OA\Property(property="latitude", type="number", format="float", description="Guess latitude", example=37.77493),
 *     @OA\Property(property="longitude", type="number", format="float", description="Guess longitude", example=-122.41942),
 *     @OA\Property(property="error_distance", type="number", format="float", description="Error distance"),
 *     @OA\Property(property="user_id", type="integer", description="User ID"),
 *     @OA\Property(property="location_id", type="integer", description="Location ID"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when guess was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when guess was last updated")
 * )
 */
class Guesses extends Model
{
    use HasFactory;

    protected $fillable = [
        'latitude', 
        'longitude', 
        'error_distance', 
        'user_id', 
        'location_id',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
