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
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use Laravel\Passport\Http\Controllers\ClientController;
use Laravel\Passport\Http\Controllers\ScopeController;

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

Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])->middleware('throttle');

Route::middleware(['auth:api'])->group(function () {

    // Location Routes
    Route::apiResource('locations', LocationController::class);
    Route::get('/location/random', [LocationController::class, 'random']);
    Route::get('/users/{user_id}/locations', [LocationController::class, 'getLocationsByUserId'])
        ->name('users.locations');

    // Profile Routes
    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('logout', [ProfileController::class, 'logout']);

    // User Update Routes
    Route::put('/update', [UpdateUserController::class, 'update']);
    Route::put('/update-password', [UpdatePasswordController::class, 'updatePassword']);
    Route::post('/users/{id}/add-points', [UpdateUserController::class, 'addPoints'])
        ->middleware(['can:addPoints,App\Models\User']); // Extra authorization
    Route::post('/update-profile-picture', [UpdateUserController::class, 'updateProfilePicture']);


    // Guess Routes
    Route::get('/guesses/best', [GuessController::class, 'getBestGuesses']);
    Route::get('/guesses/{id}', [GuessController::class, 'getBestGuessesByLocation']);

    // User Action Logging Routes
    Route::post('user-actions', [UserActionController::class, 'store']); // Log user actions

    // Admin Routes (Restrict access to admins)
    Route::middleware('can:viewActions')->group(function () {
        Route::get('admin/user-actions', [UserActionController::class, 'recent']); // View last 100 actions
    });
});