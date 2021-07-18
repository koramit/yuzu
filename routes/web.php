<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ResourceEmployeesController;
use App\Http\Controllers\ResourcePatientsController;
use App\Http\Controllers\VisitExamQueueController;
use App\Http\Controllers\VisitsController;
use App\Http\Controllers\VisitScreenQueueController;
use App\Http\Controllers\VisitSwabQueueController;
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
Route::get('terms-and-policies', [PagesController::class, 'terms'])
     ->name('terms');

// home
Route::get('/', HomeController::class)
     ->middleware('auth')
     ->name('home');

visit
Route::get('visits', [VisitsController::class, 'index'])
     ->middleware('auth', 'remember')
     ->name('visits');
Route::post('visits', [VisitsController::class, 'store'])
     ->middleware('auth')
     ->name('visits.store');
Route::get('visits/{visit:slug}/edit', [VisitsController::class, 'edit'])
     ->middleware('auth')
     ->name('visits.edit');
Route::patch('visits/{visit:slug}', [VisitsController::class, 'update'])
     ->middleware('auth')
     ->name('visits.update');

Route::get('visits/screen-queue', [VisitScreenQueueController::class, 'index'])
     ->middleware('auth')
     ->name('visits.screen-queue');
Route::get('visits/exam-queue', [VisitExamQueueController::class, 'index'])
     ->middleware('auth')
     ->name('visits.exam-queue');
Route::patch('visits/exam-queue/{visit:slug}', [VisitExamQueueController::class, 'store'])
     ->middleware('auth')
     ->name('visits.exam-queue.store');
Route::get('visits/swab-queue', [VisitSwabQueueController::class, 'index'])
     ->middleware('auth')
     ->name('visits.swab-queue');
Route::patch('visits/swab-queue/{visit:slug}', [VisitSwabQueueController::class, 'store'])
     ->middleware('auth')
     ->name('visits.swab-queue.store');

// resources
Route::middleware('auth')
     ->prefix('resources/api')
     ->name('resources.api.')
     ->group(function () {
         Route::get('patients/{hn}', ResourcePatientsController::class)
              ->name('patients.show');
         Route::get('employees/{id}', ResourceEmployeesController::class)
              ->name('employees.show');
         // Route::get('admissions/{an}', AdmissionsController::class)
          //      ->name('admissions.show');
          // Route::get('wards', WardsController::class)
          //      ->name('wards');
     });
