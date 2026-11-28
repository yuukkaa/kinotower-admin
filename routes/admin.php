<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryFilmController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\RatingFilmController;
use App\Http\Controllers\ReviewFilmController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::resource('countries', CountryController::class)
        ->except(['show'])
        ->names('admin.countries');

    Route::resource('categories', CategoryController::class)
        ->except(['show'])
        ->names('admin.categories');

    Route::resource('films', FilmController::class)
        ->except(['show'])
        ->names('admin.films');

    Route::resource('films.categories', CategoryFilmController::class)
        ->only(['index', 'create', 'store', 'destroy'])
        ->names('admin.films.categories');

    Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::patch('users/{user}/restore', [UserController::class, 'restore'])
        ->withTrashed()
        ->name('admin.users.restore');

    Route::get('reviews', [ReviewFilmController::class, 'index'])->name('admin.reviews.index');
    Route::get('films/{film}/reviews', [ReviewFilmController::class, 'index'])->name('admin.films.reviews.index');
    Route::patch('reviews/{review}/approved', [ReviewFilmController::class, 'approved'])->name('admin.reviews.approved');
    Route::delete('reviews/{review}', [ReviewFilmController::class, 'destroy'])->name('admin.reviews.destroy');

    Route::get('ratings', [RatingFilmController::class, 'index'])->name('admin.ratings.index');
    Route::get('films/{film}/ratings', [RatingFilmController::class, 'index'])->name('admin.films.ratings.index');
    Route::delete('ratings/{rating}', [RatingFilmController::class, 'destroy'])->name('admin.ratings.destroy');
});
