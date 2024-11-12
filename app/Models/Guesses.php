<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
