<?php

namespace App\Repositories;

use App\Models\Location;
use App\Interfaces\CrudeInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Exception;
use Illuminate\Support\Facades\Log;

class LocationRepository implements CrudeInterface
{
    /**
     * The ImageUploadService instance.
     *
     * @var \App\Services\ImageUploadService
     */
    protected ImageUploadService $imageUploadService;

    /**
     * Create a new repository instance.
     *
     * @param \App\Services\ImageUploadService $imageUploadService
     * @return void
     */
    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Retrieve all locations with pagination.
     *
     * @param int|null $perPage
     * @param string $orderBy
     * @param string $orderDirection
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function getAll(?int $perPage = 10, string $orderBy = 'created_at', string $orderDirection = 'desc'): Paginator
    {
        return Location::orderBy($orderBy, $orderDirection)->paginate($perPage);
    }

    /**
     * Retrieve a location by its ID.
     *
     * @param int $id
     * @return \App\Models\Location|null
     */
    public function getById(int $id): ?Location
    {
        return Location::find($id);
    }

    /**
     * Create a new location.
     *
     * @param array $data
     * @return \App\Models\Location|null
     * @throws \Exception
     */
    public function create(array $data): ?Location
    {
        try {
            // Handle image upload if present
            if ($this->hasImage($data)) {
                $data['image'] = $this->uploadImage($data['image']);
            }

            // Prepare data with user ID
            $data = $this->prepareForDB($data);

            return Location::create($data);
        } catch (Exception $e) {
            // Log the error
            Log::error('Location creation failed: ' . $e->getMessage());
            throw new Exception('Failed to create location.');
        }
    }

    /**
     * Update an existing location.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\Location|null
     * @throws \Exception
     */
    public function update(int $id, array $data): ?Location
    {
        try {
            $location = $this->getById($id);

            if (!$location) {
                throw new ModelNotFoundException("Location not found.");
            }

            // Handle image update if a new image is provided
            if ($this->hasImage($data)) {
                $data['image'] = $this->updateImage($location, $data['image']);
            }

            // Update the location with prepared data
            $location->update($this->prepareForDB($data));

            return $location;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            // Log the error
            Log::error('Location update failed: ' . $e->getMessage());
            throw new Exception('Failed to update location.');
        }
    }

    /**
     * Delete a location.
     *
     * @param int $id
     * @return \App\Models\Location|null
     * @throws \Exception
     */
    public function delete(int $id): ?Location
    {
        try {
            $location = $this->getById($id);

            if (!$location) {
                throw new ModelNotFoundException("Location not found.");
            }

            // Delete the image file if it exists
            if ($location->image) {
                $this->imageUploadService->deleteImage($location->image, 'public');
            }

            $location->delete();

            return $location;
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (Exception $e) {
            // Log the error
            Log::error('Location deletion failed: ' . $e->getMessage());
            throw new Exception('Failed to delete location.');
        }
    }

    /**
     * Fetch a random location.
     *
     * @return \App\Models\Location|null
     */
    public function getRandom(): ?Location
    {
        return Location::inRandomOrder()->first();
    }

    /**
     * Retrieve locations by User ID with pagination.
     *
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLocationsByUserId(int $userId, int $perPage = 10)
    {
        return Location::where('user_id', $userId)->paginate($perPage);
    }

    /**
     * Check if image is present and valid in the data array.
     *
     * @param array $data
     * @return bool
     */
    protected function hasImage(array $data): bool
    {
        return isset($data['image']) && $data['image'] instanceof UploadedFile && $data['image']->isValid();
    }

    /**
     * Upload an image using ImageUploadService.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     * @throws \Exception
     */
    protected function uploadImage(UploadedFile $file): string
    {
        return $this->imageUploadService->uploadImage($file, 'images/locations');
    }

    /**
     * Update an image: delete the old one and upload the new one.
     *
     * @param \App\Models\Location $location
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     * @throws \Exception
     */
    protected function updateImage(Location $location, UploadedFile $file): string
    {
        // Delete the old image if it exists
        if ($location->image) {
            $this->imageUploadService->deleteImage($location->image, 'public');
        }

        // Upload the new image
        return $this->uploadImage($file);
    }

    /**
     * Prepare data for database operations.
     *
     * @param array $data
     * @return array
     */
    protected function prepareForDB(array $data): array
    {
        // Add authenticated user ID to the data
        $data['user_id'] = Auth::id();

        // Optionally, hash the password if it's present
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        return $data;
    }

    public function deleteImage(int $id): void
    {
        $location = $this->getById($id);

        if (!$location) {
            throw new ModelNotFoundException("Location not found.");
        }

        if ($location->image) {
            $this->imageUploadService->deleteImage($location->image, 'public');
            $location->image = null;
            $location->save();
        }
    }
}