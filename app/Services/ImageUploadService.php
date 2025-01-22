<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ImageUploadService
{
    /**
     * Upload an image to the specified directory.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @param string|null $disk
     * @return string
     * @throws \Exception
     */
    public function uploadImage(UploadedFile $file, string $directory = 'images', string $disk = 'public'): string
    {
        try {
            // Generate a unique filename using UUID and the original extension
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

            // Store the file and get its path
            $path = $file->storeAs($directory, $filename, $disk);

            return $path;
        } catch (Exception $e) {
            // Log the error or handle it as per your application's requirements
            throw new Exception('Image upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete an image from storage.
     *
     * @param string $path
     * @param string|null $disk
     * @return bool
     */
    public function deleteImage(string $path, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Get the full URL of an image.
     *
     * @param string $path
     * @param string|null $disk
     * @return string|null
     */
    public function getImageUrl(string $path, string $disk = 'public'): ?string
    {
        if (Storage::disk($disk)->exists($path)) {
            return asset("storage/{$path}");
        }

        return null;
    }
}