<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserActionController;
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
    Route::apiResource('products', LocationController::class);
    Route::get('location/random', [LocationController::class, 'random']);
    
    // User Action Logging Routes
    Route::post('user-actions', [UserActionController::class, 'store']); // Log user actions

    // Admin Routes
    Route::middleware('can:viewActions')->group(function () { // Restrict access to admins
        Route::get('admin/user-actions', [UserActionController::class, 'recent']); // View last 100 actions
    });
});