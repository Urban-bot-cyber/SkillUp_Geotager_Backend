<?php

namespace App\Repositories;

use App\Models\Location;
use App\Interfaces\CrudeInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LocationRepository implements CrudeInterface
{
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
        // Handle image upload if present
        $data = $this->handleImageUpload($data);

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

        // Handle image update and deletion of the old image if a new one is uploaded
        $data = $this->handleImageUpdate($location, $data);

        $location->update($this->prepareForDB($data));
        return $location;
    }

    public function delete(int $id): ?Location
    {
        $location = $this->getById($id);

        if (!$location) {
            throw new ModelNotFoundException("Location not found.");
        }

        // Delete the image file if it exists
        $this->deleteImage($location->image);

        $location->delete();
        return $location;
    }

    /**
     * Store the image and return the path
     */
    protected function storeImage($file): string
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('images/locations', $fileName, 'public');
    }

    /**
     * Handle image upload in create or update method
     */
    protected function handleImageUpload(array $data): array
    {
        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image'] = $this->storeImage($data['image']);
        }
        return $data;
    }

    /**
     * Handle image update and delete old image if necessary
     */
    protected function handleImageUpdate(Location $location, array $data): array
    {
        if (isset($data['image']) && $data['image']->isValid()) {
            $this->deleteImage($location->image); // Delete old image if exists
            $data['image'] = $this->storeImage($data['image']); // Store new image
        }
        return $data;
    }

    /**
     * Delete image from storage
     */
    protected function deleteImage(?string $imagePath): void
    {
        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }
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
}
