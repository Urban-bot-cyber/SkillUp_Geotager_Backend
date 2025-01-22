<?php

namespace App\Repositories;

use App\Models\Location;
use App\Interfaces\CrudeInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageUploadService;

class LocationRepository implements CrudeInterface
{
     /**
     * The ImageUploadService instance.
     *
     * @var \App\Services\ImageUploadService
     */
    protected $imageUploadService;

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

    public function getAll(?int $perPage = 10, string $orderBy = 'created_at', string $orderDirection = 'desc'): Paginator
{
    return Location::orderBy($orderBy, $orderDirection)->paginate($perPage);
}

    public function getById(int $id): ?Location
    {
        return Location::find($id);
    }

    public function create(array $data): ?Location
    {
        // Handle image upload if present using ImageUploadService
        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image'] = $this->imageUploadService->uploadImage($data['image'], 'images/locations');
        }

        // Prepare data with user ID
        $data = $this->prepareForDB($data);

        return Location::create($data);
    }

    protected function prepareForDB(array $data): array
    {
        // Add authenticated user ID to the data
        $data['user_id'] = Auth::id();
        return $data;
    }

    public function update(int $id, array $data): ?Location
    {
        $location = $this->getById($id);

        if (!$location) {
            throw new ModelNotFoundException("Location not found.");
        }

        // Handle image update and deletion of the old image if a new one is uploaded using ImageUploadService
        if (isset($data['image']) && $data['image']->isValid()) {
            // Delete old image
            if ($location->image) {
                $this->imageUploadService->deleteImage($location->image, 'public');
            }

            // Upload new image
            $data['image'] = $this->imageUploadService->uploadImage($data['image'], 'images/locations');
        }

        $location->update($this->prepareForDB($data));
        return $location;
    }

    public function delete(int $id): ?Location
    {
        $location = $this->getById($id);
    
        if (!$location) {
            throw new ModelNotFoundException("Location not found.");
        }
    
        // Delete the image file if it exists using ImageUploadService
        if ($location->image) {
            $this->imageUploadService->deleteImage($location->image, 'public');
        }
    
        $location->delete();
        return $location;
    }

    /**
     * Fetch a random location.
     *
     * @return Location|null
     */
    public function getRandom()
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

}
