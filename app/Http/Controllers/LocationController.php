<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationCreateRequest;
use App\Http\Requests\LocationUpdateRequest;
use App\Models\Location;
use App\Repositories\LocationRepository;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    use ResponseTrait;

    protected $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * Display a paginated list of locations.
     *
     * @OA\Get(
     *     path="/api/locations",
     *     tags={"Locations"},
     *     summary="Get all locations",
     *     description="Fetch all locations with pagination",
     *     operationId="index",
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Number of items per page for pagination.",
     *         required=false,
     *         @OA\Schema(type="integer", default=40)
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(response=200, description="Locations fetched successfully")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('perPage', 10);
            $locations = $this->locationRepository->getAll($perPage, 'created_at', 'desc'); // Order by created_at desc
            return $this->responseSuccess($locations, 'Locations fetched successfully.');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseError([], 'Failed to fetch locations.');
        }
    }

    /**
     * Create a new location with image upload.
     *
     * @OA\Post(
     *     path="/api/locations",
     *     tags={"Locations"},
     *     summary="Create location",
     *     description="Create a new location with image upload",
     *     operationId="store",
     *     security={{"bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="image", description="Location image", type="string", format="binary"),
     *                 @OA\Property(property="latitude", description="Location latitude", type="number", format="float"),
     *                 @OA\Property(property="longitude", description="Location longitude", type="number", format="float"),
     *                 @OA\Property(property="user_id", description="User ID associated with location", type="integer"),
     *                 required={"latitude", "longitude", "user_id"}
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Location created successfully")
     * )
     */
    public function store(LocationCreateRequest $request): JsonResponse
    {
        try {
            $data = $request->all();
            $location = $this->locationRepository->create($data);
            return $this->responseSuccess($location, 'Location created successfully.');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseError([], 'Failed to create location.');
        }
    }

    /**
     * Update an existing location with optional image upload.
     *
     * @OA\Put(
     *     path="/api/locations/{id}",
     *     tags={"Locations"},
     *     summary="Update location",
     *     description="Update an existing location with optional image upload",
     *     operationId="updateLocation",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(name="id", in="path", description="Location ID", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="image", description="Location image", type="string", format="binary"),
     *                 @OA\Property(property="latitude", description="Location latitude", type="number", format="float"),
     *                 @OA\Property(property="longitude", description="Location longitude", type="number", format="float"),
     *                 @OA\Property(property="user_id", description="User ID associated with location", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Location updated successfully")
     * )
     */
    public function update(LocationUpdateRequest $request, Location $location): JsonResponse
    {
        try {
            $data = $request->all();
            $updatedLocation = $this->locationRepository->update($location->id, $data);
            return $this->responseSuccess($updatedLocation, 'Location updated successfully.');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseError([], 'Failed to update location.');
        }
    }

    /**
     * Delete an existing location along with its associated image.
     *
     * @OA\Delete(
     *     path="/api/locations/{id}",
     *     tags={"Locations"},
     *     summary="Delete location",
     *     description="Delete an existing location and its associated image",
     *     operationId="destroy",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(name="id", in="path", description="Location ID", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Location deleted successfully")
     * )
     */
    public function destroy(Location $location): JsonResponse
    {
        try {
            $deletedLocation = $this->locationRepository->delete($location->id);
            return $this->responseSuccess([], 'Location deleted successfully.');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseError([], 'Failed to delete location.');
        }
    }

    /**
    * Get a random location.
    *
    * @OA\Get(
    *     path="/api/location/random",
    *     tags={"Locations"},
    *     summary="Get a random location",
    *     description="Fetch a single random location",
    *     operationId="random",
    *     security={{"bearer":{}}},
    *     @OA\Response(
    *         response=200,
    *         description="Random location fetched successfully",
    *         @OA\JsonContent(ref="#/components/schemas/Location")
    *     ),
    *     @OA\Response(response=404, description="No locations available")
    * )
    */
    public function random(): JsonResponse
    {
        try {
            $location = $this->locationRepository->getRandom();
            if ($location) {
                return $this->responseSuccess($location, 'Random location fetched successfully.');
            } else {
                return $this->responseError([], 'No locations available.', 404);
            }
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseError([], 'Failed to fetch random location.');
        }
    }
}
