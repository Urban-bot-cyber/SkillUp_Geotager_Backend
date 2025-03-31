<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuessCreateRequest;
use App\Models\Guesses;
use App\Models\Location;
use App\Models\User; // Ensure the User model is imported
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Import DB for transactions
use Illuminate\Support\Facades\Log; // Import Log facade

class GuessController extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Post(
     *     path="/api/locations/guess/{id}",
     *     tags={"Guesses"},
     *     summary="Submit a guess for a location",
     *     description="Allows a user to guess the location's coordinates and calculates the error distance. Subtracts points based on the number of previous guesses.",
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
     *     @OA\Response(response=404, description="Location not found")
     * )
     */
    public function guessLocation(GuessCreateRequest $request, int $id): JsonResponse
    { 
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->responseError('Authenticated user not found.', 401);
        }
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
       
        // Fetch the location with its image
        $locationId = $request->input('location_id');
        $location = Location::select('id', 'latitude', 'longitude', 'image')->find($locationId);

        if (!$location) {
            abort(404, 'Location not found.');
        }

        DB::beginTransaction();

        try {
            $previousGuessesCount = Guesses::where('location_id', $id)
                ->where('user_id', $user->id)
                ->count();

            $pointsToSubtract = match ($previousGuessesCount) {
                0 => 1,
                1 => 2,
                default => 3,
            };

            if ($user->points < $pointsToSubtract) {
                DB::rollBack();
                return $this->responseError('Insufficient points to make a guess.', 400);
            }

            $user->points -= $pointsToSubtract;
            $user->save();

            $distance = $this->calculateDistance(
                $location->latitude,
                $location->longitude,
                $latitude,
                $longitude
            );

            $guess = Guesses::create([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'error_distance' => $distance,
                'user_id' => $user->id,
                'location_id' => $location->id,
            ]);

            DB::commit();

            // Include location image in the response
            $guess->load('location:id,image');

            return $this->responseSuccess($guess, 'Guess submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseError('An error occurred while submitting the guess.', 500);
        }
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

    /**
     * @OA\Get(
     *     path="/api/guesses/best",
     *     tags={"Guesses"},
     *     summary="Retrieve the best guesses for the authenticated user",
     *     description="Returns a list of the user's guesses ordered by smallest error distance.",
     *     operationId="getBestGuesses",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of top guesses to retrieve",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved best guesses",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Guess")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getBestGuesses(): JsonResponse
    {
        $user = Auth::user();
        $limit = request()->query('limit', 10);
    
        // Fetch the best guesses with associated location images
        $bestGuesses = Guesses::with('location:id,image')
            ->where('user_id', $user->id)
            ->orderBy('error_distance', 'asc')
            ->limit($limit)
            ->get();
    
        return $this->responseSuccess($bestGuesses, 'Best guesses retrieved successfully.');
    }

    /**
     * @OA\Get(
     *     path="/api/guesses/{id}",
     *     tags={"Guesses"},
     *     summary="Retrieve the best guesses from each unique user for a specific location",
     *     description="Returns the best guess (smallest error distance) from each user for the specified location.",
     *     operationId="getBestGuessesByLocation",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the location",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of top guesses to retrieve per user",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved best guesses",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Guess")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Location not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getBestGuessesByLocation(int $id): JsonResponse
    {
        $user = Auth::user();
        $location = Location::find($id);
    
        if (!$location) {
            return $this->responseError('Location not found.', 404);
        }
    
        try {
            $subQuery = Guesses::select('user_id', DB::raw('MIN(error_distance) as min_error_distance'))
                ->where('location_id', $id)
                ->groupBy('user_id');
    
            $bestGuesses = Guesses::joinSub($subQuery, 'best_guesses', function ($join) {
                    $join->on('guesses.user_id', '=', 'best_guesses.user_id')
                         ->on('guesses.error_distance', '=', 'best_guesses.min_error_distance');
                })
                ->where('guesses.location_id', $id)
                ->select('guesses.*')
                ->with(['location:id,image', 'user:id,first_name,last_name,profile_picture']) // <-- Include user
                ->get();
    
            return $this->responseSuccess($bestGuesses, 'Best guesses retrieved successfully.');
        } catch (\Exception $e) {
            Log::error('Error retrieving best guesses: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return $this->responseError('An error occurred while retrieving best guesses.', 500);
        }
    }
}