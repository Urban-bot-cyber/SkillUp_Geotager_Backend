<?php

namespace App\Repositories;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\PersonalAccessTokenResult;

class AuthRepository
{
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

    public function register(array $data): array
    {
        // Handle profile picture upload if provided
        if (isset($data['profile_picture']) && $data['profile_picture']->isValid()) {
            $data['profile_picture'] = $this->storeProfilePicture($data['profile_picture']);
        }

        $user = User::create($this->prepareDataForRegistration($data));

        if (!$user) {
            throw new Exception("User registration failed. Please try again.", 500);
        }

        $tokenInstance = $this->createAuthToken($user);

        return $this->getAuthData($user, $tokenInstance);
    }

    protected function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    protected function isValidPassword(User $user, array $data): bool
    {
        return Hash::check($data['password'], $user->password);
    }

    protected function createAuthToken(User $user): PersonalAccessTokenResult
    {
        return $user->createToken('authToken');
    }

    protected function getAuthData(User $user, PersonalAccessTokenResult $tokenInstance): array
    {
        return [
            'user'         => $user,
            'access_token' => $tokenInstance->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => Carbon::parse($tokenInstance->token->expires_at)->toDateTimeString(),
        ];
    }

    protected function prepareDataForRegistration(array $data): array
    {
        return [
            'first_name'      => $data['first_name'],
            'last_name'       => $data['last_name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'profile_picture' => $data['profile_picture'] ?? null,
        ];
    }

    protected function storeProfilePicture($file): string
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('public/images/profile_pictures', $fileName);
    }
}
