<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PrintOPDCardController;
use App\Http\Controllers\ResourceEmployeesController;
use App\Http\Controllers\ResourcePatientsController;
use App\Http\Controllers\ServerSendEventsController;
use App\Http\Controllers\VisitAttachOPDCardController;
use App\Http\Controllers\VisitAuthorizationController;
use App\Http\Controllers\VisitDischargeListController;
use App\Http\Controllers\VisitEvaluationListController;
use App\Http\Controllers\VisitExamListController;
use App\Http\Controllers\VisitMedicalRecordListController;
use App\Http\Controllers\VisitPrintoutController;
use App\Http\Controllers\VisitsController;
use App\Http\Controllers\VisitScreenListController;
use App\Http\Controllers\VisitSwabListController;
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

// screen list
Route::get('visits/screen-list', [VisitScreenListController::class, 'index'])
     ->middleware('auth', 'can:view_screen_list')
     ->name('visits.screen-list');

// exam list
Route::get('visits/exam-list', [VisitExamListController::class, 'index'])
     ->middleware('auth', 'can:view_exam_list')
     ->name('visits.exam-list');
Route::patch('visits/exam-list/{visit:slug}', [VisitExamListController::class, 'store'])
     ->middleware('auth', 'can:update,visit')
     ->name('visits.exam-list.store');

// swab list
Route::get('visits/swab-list', [VisitSwabListController::class, 'index'])
     ->middleware('auth', 'can:view_swab_list')
     ->name('visits.swab-list');
Route::patch('visits/swab-list/{visit:slug}', [VisitSwabListController::class, 'store'])
     ->middleware('auth', 'can:update,visit')
     ->name('visits.swab-list.store');

//discharge
Route::patch('visits/discharge-list/{visit:slug}', [VisitDischargeListController::class, 'store'])
     ->middleware('auth', 'can:discharge,visit')
     ->name('visits.discharge-list.store');

// medical record
Route::get('visits/mr-list', [VisitMedicalRecordListController::class, 'index'])
     ->middleware('auth', 'can:view_mr_list')
     ->name('visits.mr-list');

Route::post('visits/authorize/{visit:slug}', [VisitAuthorizationController::class, 'store'])
     ->middleware('auth', 'can:authorize_visit')
     ->name('visits.authorize.store');
// Route::delete('visits/authorize/{visit:slug}', [VisitAuthorizationController::class, 'destroy'])
//      ->middleware('auth', 'can:authorize_visit')
//      ->name('visits.authorize.store');
Route::post('visits/attach-opd-card/{visit:slug}', [VisitAttachOPDCardController::class, 'store'])
     ->middleware('auth', 'can:attach_opd_card')
     ->name('visits.attach-opd-card.store');
// Route::delete('visits/attach-opd-card/{visit:slug}', [VisitAttachOPDCardController::class, 'destroy'])
//      ->middleware('auth', 'can:attach_opd_card')
//      ->name('visits.attach-opd-card.store');

//evaluation
Route::get('visits/evaluation-list', [VisitEvaluationListController::class, 'index'])
     ->middleware('auth', 'can:view_evaluation_list')
     ->name('visits.evaluation-list');

// visit
Route::get('visits', [VisitsController::class, 'index'])
     ->middleware('auth', 'remember', 'can:view_any_visits')
     ->name('visits');
Route::post('visits', [VisitsController::class, 'store'])
     ->middleware('auth', 'can:create_visit')
     ->name('visits.store');
Route::get('visits/{visit:slug}/edit', [VisitsController::class, 'edit'])
     ->middleware('auth', 'can:update,visit')
     ->name('visits.edit');
Route::patch('visits/{visit:slug}', [VisitsController::class, 'update'])
     ->middleware('auth', 'can:update,visit')
     ->name('visits.update');
Route::get('visits/{visit:slug}', [VisitsController::class, 'show'])
     ->middleware('auth', 'can:view,visit')
     ->name('visits.show');
Route::get('visits/{visit:slug}/replace', [VisitsController::class, 'replace'])
     ->middleware('auth', 'can:replace,visit')
     ->name('visits.replace');
Route::get('print-opd-card/{visit:slug}', PrintOPDCardController::class)
     ->middleware('auth')
     ->name('print-opd-card');
// Route::put('visits/{visit:slug}', [VisitsController::class, 'put'])
//      ->middleware('auth', 'can:replace,visit')
//      ->name('visits.put');

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

// server push
Route::get('sse', ServerSendEventsController::class)
     ->middleware('auth')
     ->name('sse');

// test role
Route::get('login-as/{role}', function ($role) {
    $user = \App\Models\User::whereName($role)->first();
    \Auth::login($user);

    return redirect(route($user->home_page));
});
