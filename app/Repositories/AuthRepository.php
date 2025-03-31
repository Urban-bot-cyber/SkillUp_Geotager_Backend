<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\PersonalAccessTokenResult;
use App\Services\ImageUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthRepository
{
    protected ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Handle user login.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function login(array $data): array
    {
        $user = $this->getUserByEmail($data['email']);

        if (!$user) {
            throw new Exception("User does not exist.", 404);
        }

        if (!$this->isValidPassword($user, $data)) {
            throw new Exception("Passwords do not match.", 401);
        }

        $tokenInstance = $this->createAuthToken($user);

        return $this->getAuthData($user, $tokenInstance);
    }

    /**
     * Handle user registration.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function register(array $data): array
    {
        DB::beginTransaction();

        try {
            // Handle profile picture upload if provided
            if (isset($data['profile_picture']) && $data['profile_picture']->isValid()) {
                $data['profile_picture'] = $this->imageUploadService->uploadImage(
                    $data['profile_picture'],
                    'images/profile_pictures'
                );
            }

            $user = User::create($this->prepareDataForRegistration($data));

            if (!$user) {
                throw new Exception("User registration failed. Please try again.", 500);
            }

            $tokenInstance = $this->createAuthToken($user);

            DB::commit();

            return $this->getAuthData($user, $tokenInstance);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle user update.
     *
     * @param array $data
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public function update(array $data, int $userId): array
    {
        DB::beginTransaction();

        try {
            // Get the user by ID
            $user = $this->getUserById($userId);

            if (!$user) {
                throw new Exception("User does not exist.", 404);
            }

            // Handle profile picture update if provided
            if (isset($data['profile_picture']) && $data['profile_picture']->isValid()) {
                // Remove old profile picture if exists
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    $this->imageUploadService->deleteImage($user->profile_picture, 'public');
                }

                // Upload the new profile picture and update the path
                $data['profile_picture'] = $this->imageUploadService->uploadImage(
                    $data['profile_picture'],
                    'images/profile_pictures'
                );
            }

            // Update user data
            $preparedData = $this->prepareDataForUpdate($data);
            Log::info('Prepared update data:', $preparedData);
            $user->update($this->prepareDataForUpdate($data));

            // Refresh the user instance to get the latest data
            $user->refresh();

            // Create a new authentication token if necessary
            $tokenInstance = $this->createAuthToken($user);

            DB::commit();

            return $this->getAuthData($user, $tokenInstance);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add points to a user by their ID.
     *
     * @param int $userId
     * @param int $points
     * @return array
     * @throws Exception
     */
    public function addPoints(int $userId, int $points = 10): array
    {
        DB::beginTransaction();

        try {Log::info("Adding points to user ID: " . $userId);
            // Retrieve the user by ID or throw a 404 ModelNotFoundException
            $user = User::findOrFail($userId);

            // Increment the user's points
            $user->increment('points', (int) $points);

            // Refresh the user instance to get the latest points value
            $user->refresh();

            DB::commit();

            return [
                'user_id'    => $user->id,
                'new_points' => (int) $user->points,
            ];
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new ModelNotFoundException("User not found.", 404);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update the user's password.
     *
     * @param array $data
     * @param int $userId
     * @return User
     * @throws Exception
     */
    public function updatePassword(array $data, int $userId): User
    {
        $user = User::findOrFail($userId);

        // The current_password validation ensures the current password is correct,
        // so no need to re-validate it here.

        $user->password = Hash::make($data['password']);
        $user->save();

        return $user;
    }

    /**
     * Retrieve a user by their email.
     *
     * @param string $email
     * @return User|null
     */
    protected function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Retrieve a user by their ID.
     *
     * @param int $userId
     * @return User|null
     */
    protected function getUserById(int $userId): ?User
    {
        return User::find($userId);
    }

    /**
     * Validate the user's password.
     *
     * @param User $user
     * @param array $data
     * @return bool
     */
    protected function isValidPassword(User $user, array $data): bool
    {
        return Hash::check($data['password'], $user->password);
    }

    /**
     * Create an authentication token for the user.
     *
     * @param User $user
     * @return PersonalAccessTokenResult
     */
    protected function createAuthToken(User $user): PersonalAccessTokenResult
    {
        return $user->createToken('authToken');
    }

    /**
     * Prepare authentication data for response.
     *
     * @param User $user
     * @param PersonalAccessTokenResult $tokenInstance
     * @return array
     */
    protected function getAuthData(User $user, PersonalAccessTokenResult $tokenInstance): array
    {
        return [
            'user'         => $user,
            'access_token' => $tokenInstance->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => Carbon::parse($tokenInstance->token->expires_at)->toDateTimeString(),
        ];
    }

    /**
     * Prepare data for user registration.
     *
     * @param array $data
     * @return array
     */
    protected function prepareDataForRegistration(array $data): array
    {
        return [
            'first_name'      => $data['first_name'],
            'last_name'       => $data['last_name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'profile_picture' => $data['profile_picture'] ?? null,
            'points'          => 10, // Initialize points to 10
        ];
    }

    /**
     * Prepare data for user update.
     *
     * @param array $data
     * @return array
     */
    protected function prepareDataForUpdate(array $data): array
    {
        $updatedData = [
            'first_name'       => $data['first_name'] ?? null,
            'last_name'        => $data['last_name'] ?? null,
            'email'            => $data['email'] ?? null,
            'password'         => isset($data['password']) ? Hash::make($data['password']) : null,
            'profile_picture'  => $data['profile_picture'] ?? null,
        ];

        // Remove null values to avoid overwriting with null if not provided
        return array_filter($updatedData, function ($value) {
            return !is_null($value);
        });
    }

    /**
     * Update the profile picture of a user.
     *
     * @param int $userId
     * @param string $newPath
     * @return string
     * @throws Exception
     */
    public function updateProfilePicture(int $userId, string $newPath): string
    {
        $user = User::find($userId);

        if (!$user) {
            throw new Exception('User not found.', 404);
        }

        // Optionally, retrieve the old profile picture path
        $oldPath = $user->profile_picture;

        // Update with the new path
        $user->profile_picture = $newPath;
        $user->save();

        // Optionally, delete the old image if it exists
        if ($oldPath) {
            $this->imageUploadService->deleteImage($oldPath, 'public');
        }

        return asset('storage/' . $newPath); // Return the full URL
    }
}
