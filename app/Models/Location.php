<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *     schema="Location",
 *     type="object",
 *     title="Location",
 *     required={"id", "latitude", "longitude", "user_id"},
 *     @OA\Property(property="id", type="integer", description="Location ID"),
 *     @OA\Property(property="image", type="string", format="binary", description="Location image"),
 *     @OA\Property(property="latitude", type="number", format="float", description="Latitude"),
 *     @OA\Property(property="longitude", type="number", format="float", description="Longitude"),
 *     @OA\Property(property="user_id", type="integer", description="User ID associated with the location"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp")
 * )
 */
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'image', 
        'latitude', 
        'longitude',
        'user_id',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guesses()
    {
        return $this->hasMany(Guesses::class);
    }
}
