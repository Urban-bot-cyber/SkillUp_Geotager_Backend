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

Route::middleware('auth:api')->group(function () {
    // Location Routes
    Route::get('locations', [LocationController::class, 'index']);
    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('logout', [ProfileController::class, 'logout']);
    Route::apiResource('locations', LocationController::class);
    Route::get('location/random', [LocationController::class, 'random']);
    Route::put('/update-password', [UpdatePasswordController::class, 'update']);
    Route::get('/locations/{user_id}', [LocationController::class, 'getLocationsByUserId'])->name('users.locations');
    Route::get('/guesses/best', [GuessController::class, 'getBestGuesses']);
    Route::get('/guesses/{id}', [GuessController::class, 'getBestGuessesByLocation']);
    Route::post('/users/{id}/add-points', [UpdateUserController::class, 'addPoints'])
    ->middleware(['auth:api', 'can:addPoints,App\Models\User']); // Ensure authentication and authorization
    
    // User Action Logging Routes
    Route::post('user-actions', [UserActionController::class, 'store']); // Log user actions

    // Admin Routes
    Route::middleware('can:viewActions')->group(function () { // Restrict access to admins
        Route::get('admin/user-actions', [UserActionController::class, 'recent']); // View last 100 actions
    });
});