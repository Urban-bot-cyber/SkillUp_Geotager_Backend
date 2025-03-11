<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationCreateRequest;
use App\Http\Requests\LocationUpdateRequest;
use App\Models\Location;
Use App\Models\User;
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

    /**
     * Display a single location by ID.
     *
     * @OA\Get(
     *     path="/api/locations/{id}",
     *     tags={"Locations"},
     *     summary="Get a single location",
     *     description="Fetch a single location by its ID",
     *     operationId="findById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the location to fetch",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Location fetched successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Location")
     *     ),
     *     @OA\Response(response=404, description="Location not found")
     * )
     */
    public function findById(int $id): JsonResponse
    {
        try {
            $location = $this->locationRepository->getById($id);

            if ($location) {
                return $this->responseSuccess($location, 'Location fetched successfully.');
            } else {
                return $this->responseError([], 'Location not found.', 404);
            }
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseError([], 'Failed to fetch location.');
        }
    }

    /**
     * Retrieve locations by User ID.
     *
     * @OA\Get(
     *     path="/api/locations/{user_id}",
     *     tags={"Locations"},
     *     summary="Get locations by User ID",
     *     description="Fetch all locations associated with a specific user ID with pagination",
     *     operationId="getLocationsByUserId",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="ID of the user whose locations are to be fetched",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Number of items per page for pagination.",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Locations fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Location")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function getLocationsByUserId(Request $request, int $user_id): JsonResponse
    {
        try {
            $perPage = $request->input('perPage', 10);
            $userExists = User::find($user_id);

            if (!$userExists) {
                return $this->responseError([], 'User not found.', 404);
            }

            $locations = $this->locationRepository->getLocationsByUserId($user_id, $perPage);
            return $this->responseSuccess($locations, 'Locations fetched successfully.');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseError([], 'Failed to fetch locations.', 500);
        }
    }
    /**
     * Upload or update the image for a specific location.
     *
     * @OA\Post(
     *     path="/api/locations/{location}/image",
     *     tags={"Locations"},
     *     summary="Upload or Update Location Image",
     *     description="Uploads a new image or updates the existing image for a specific location.",
     *     operationId="uploadLocationImage",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="location",
     *         in="path",
     *         description="ID of the location",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="image",
     *                     description="Location image",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 required={"image"}
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Image uploaded successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Location not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function uploadImage(Request $request, Location $location): JsonResponse
    {
        try {
            $data = $request->all();
            $data['image'] = $request->file('image');

            $updatedLocation = $this->locationRepository->update($location->id, $data);
            return $this->responseSuccess($updatedLocation, 'Image uploaded successfully.');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseError([], 'Failed to upload image.', 500);
        }
    }

    /**
     * Delete the image associated with a specific location.
     *
     * @OA\Delete(
     *     path="/api/locations/{location}/image",
     *     tags={"Locations"},
     *     summary="Delete Location Image",
     *     description="Deletes the image associated with a specific location.",
     *     operationId="deleteLocationImage",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="location",
     *         in="path",
     *         description="ID of the location",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Image deleted successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Location or Image not found"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function deleteImage(Location $location): JsonResponse
    {
        try {
            if (!$location->image) {
                return $this->responseError([], 'No image found for this location.', 404);
            }

            $this->locationRepository->deleteImage($location->id);
            return $this->responseSuccess([], 'Image deleted successfully.');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseError([], 'Failed to delete image.', 500);
        }
    }
}
