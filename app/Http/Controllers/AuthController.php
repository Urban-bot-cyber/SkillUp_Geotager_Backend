<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            // Retrieve the user from Google (stateless if you're not using sessions)
            $googleUser = Socialite::driver('google')->user();

            // Find the user by email or create a new one.
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'first_name' => $googleUser->getName(), // Adjust if needed (e.g., split full name)
                    'last_name' => '', // If needed, you could split the name.
                    'password' => Hash::make(Str::random(16)), // Generate a random password
                ]
            );

            // Create a Passport access token for the user.
            $token = $user->createToken('Google Login')->accessToken;

            // Optionally, update other user details if needed.

            // Redirect to your frontend with the token as a query parameter.
            $frontendUrl = config('app.frontend_url'); // Ensure you've defined FRONTEND_URL in your .env
            return redirect()->to($frontendUrl . '/oauth/callback?access_token=' . $token);
        } catch (\Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage());
            return redirect()->to(config('app.frontend_url') . '/login?error=oauth');
        }
    }
}