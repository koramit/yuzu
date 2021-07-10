<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\VisitsController;
use Illuminate\Support\Facades\Route;

Route::get('/colors', function () {
    return Inertia\Inertia::render('Welcome');
});

// Auth
Route::get('login', [AuthenticatedSessionController::class, 'create'])
     ->middleware('guest')
     ->name('login');
Route::post('login', [AuthenticatedSessionController::class, 'store'])
     ->middleware('guest')
     ->name('login.store');
Route::post('check-timeout', [AuthenticatedSessionController::class, 'update'])
     ->name('check-timeout');
Route::delete('logout', [AuthenticatedSessionController::class, 'destroy'])
     ->middleware('auth')
     ->name('logout');

// Register
Route::get('register', [RegisteredUserController::class, 'create'])
     ->middleware('guest')
     ->name('register');
Route::post('register', [RegisteredUserController::class, 'store'])
     ->middleware('guest')
     ->name('register.store');

// Page
Route::get('/terms-and-policies', [PagesController::class, 'terms'])
     ->name('terms');

// Route::get('visits', [VisitsController::class, 'index']);

// visit
Route::get('/', [VisitsController::class, 'index'])
     ->middleware('auth')
     ->name('home');
