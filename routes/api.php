<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\GuessController;
use App\Http\Controllers\UserActionController;
use App\Http\Controllers\UpdatePasswordController;
use App\Http\Controllers\UpdateUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::get('auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::middleware(['auth:api'])->group(function () {

    // Location Routes
 
    Route::get('/location/random', [LocationController::class, 'random']);


    // Profile Routes
    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('logout', [ProfileController::class, 'logout']);

    // User Update Routes
    Route::put('/update', [UpdateUserController::class, 'update']);
    Route::put('/update-password', [UpdatePasswordController::class, 'updatePassword']);
    Route::post('/users/{id}/add-points', [UpdateUserController::class, 'addPoints'])->where('id', '[0-9]+');
    Route::post('/update-profile-picture', [UpdateUserController::class, 'updateProfilePicture']);

    Route::get('/locations', [LocationController::class, 'index']); // Get all locations
    Route::get('/locations/me', [LocationController::class, 'getLocationsForCurrentUser']); // Get all locations for the current user
    Route::get('/locations/{id}', [LocationController::class, 'show']); // Get a single location

    // Guess Routes
    Route::get('/guesses/best', [GuessController::class, 'getBestGuesses']);
    Route::get('/guesses/{id}', [GuessController::class, 'getBestGuessesByLocation']);
    Route::post('/locations/guess/{id}', [GuessController::class, 'guessLocation']);

    // User Action Logging Routes
    Route::post('user-actions', [UserActionController::class, 'store']); // Log user actions
    Route::get('admin/user-actions', [UserActionController::class, 'recent']); // View last 100 actions
   
});