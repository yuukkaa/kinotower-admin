<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\FilmController;
use App\Http\Controllers\Api\V1\GenderController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserRatingController;
use App\Http\Controllers\Api\V1\UserReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public endpoints
    Route::get('films', [FilmController::class, 'index']);
    Route::get('films/{id}', [FilmController::class, 'show']);
    Route::get('films/{filmId}/reviews', [FilmController::class, 'reviews']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('countries', [CountryController::class, 'index']);
    Route::get('genders', [GenderController::class, 'index']);

    Route::post('auth/signup', [AuthController::class, 'signup']);
    Route::post('auth/signin', [AuthController::class, 'signin']);

    // Protected endpoints
    Route::middleware('auth.api')->group(function () {
        Route::post('auth/signout', [AuthController::class, 'signout']);

        Route::get('users/{id}', [UserController::class, 'show']);
        Route::put('users', [UserController::class, 'update']);
        Route::delete('users', [UserController::class, 'destroy']);

        Route::post('users/{userId}/reviews', [UserReviewController::class, 'store']);
        Route::get('users/{userId}/reviews', [UserReviewController::class, 'index']);
        Route::delete('users/{userId}/reviews/{id}', [UserReviewController::class, 'destroy']);

        Route::post('users/{userId}/ratings', [UserRatingController::class, 'store']);
        Route::get('users/{userId}/ratings', [UserRatingController::class, 'index']);
        Route::delete('users/{userId}/ratings/{id}', [UserRatingController::class, 'destroy']);
    });
});
