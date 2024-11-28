<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuessCreateRequest;
use App\Models\Guesses;
use App\Models\Location;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GuessController extends Controller
{
    use ResponseTrait;

/**
 * @OA\Post(
 *     path="/api/locations/guess/{id}",
 *     tags={"Guesses"},
 *     summary="Submit a guess for a location",
 *     description="Allows a user to guess the location's coordinates and calculates the error distance.",
 *     operationId="guessLocation",
 *     security={{"bearer":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the location to guess",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"latitude", "longitude"},
 *             @OA\Property(property="latitude", type="number", format="float", description="Latitude of the guess"),
 *             @OA\Property(property="longitude", type="number", format="float", description="Longitude of the guess")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Guess submitted successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Guess")
 *     ),
 *     @OA\Response(response=400, description="Bad request"),
 *     @OA\Response(response=403, description="User has already guessed this location"),
 *     @OA\Response(response=404, description="Location not found")
 * )
 */
    public function guessLocation(GuessCreateRequest $request, int $id): JsonResponse
    {
        $user = Auth::user();

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Fetch the location
        $location = Location::find($id);

        if (!$location) {
            abort(404, 'Location not found.');
        }

        // Check if the user has already guessed this location
        $existingGuess = Guesses::where('location_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingGuess) {
            abort(403, 'You have already guessed this location.');
        }

        // Calculate the error distance
        $distance = $this->calculateDistance(
            $location->latitude,
            $location->longitude,
            $latitude,
            $longitude
        );

        // Save the guess
        $guess = Guesses::create([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'error_distance' => $distance,
            'user_id' => $user->id,
            'location_id' => $location->id,
        ]);

        return $this->responseSuccess($guess, 'Guess submitted successfully.');
    }

    /**
     * Calculate the distance between two geographical points using the Haversine formula.
     *
     * @param float $lat1 Latitude of the first point.
     * @param float $lon1 Longitude of the first point.
     * @param float $lat2 Latitude of the second point.
     * @param float $lon2 Longitude of the second point.
     * @return float Distance in meters.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Convert degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $earthRadius = 6371000; // Earth's radius in meters

        $latDiff = $lat2Rad - $lat1Rad;
        $lonDiff = $lon2Rad - $lon1Rad;

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2); // Return distance rounded to 2 decimal places
    }
}