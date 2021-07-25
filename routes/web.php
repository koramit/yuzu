<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportColabController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PrintOPDCardController;
use App\Http\Controllers\ResourceEmployeesController;
use App\Http\Controllers\ResourcePatientsController;
use App\Http\Controllers\ServerSendEventsController;
use App\Http\Controllers\VisitAttachOPDCardController;
use App\Http\Controllers\VisitAuthorizationController;
use App\Http\Controllers\VisitDischargeListController;
use App\Http\Controllers\VisitEvaluateController;
use App\Http\Controllers\VisitExamListController;
use App\Http\Controllers\VisitExportController;
use App\Http\Controllers\VisitMedicalRecordListController;
use App\Http\Controllers\VisitsController;
use App\Http\Controllers\VisitScreenListController;
use App\Http\Controllers\VisitSwabListController;
use Illuminate\Support\Facades\Route;

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

// discharge from exam
Route::post('visits/discharge-list/{visit:slug}', [VisitDischargeListController::class, 'store'])
     ->middleware('auth', 'can:discharge,visit')
     ->name('visits.discharge-list.store');
// discharge from swab
Route::patch('visits/discharge-list/{visit:slug}', [VisitDischargeListController::class, 'update'])
     ->middleware('auth', 'can:discharge,visit')
     ->name('visits.discharge-list.update');

// medical record
Route::get('visits/mr-list', [VisitMedicalRecordListController::class, 'index'])
     ->middleware('auth', 'can:view_mr_list')
     ->name('visits.mr-list');
Route::post('visits/authorize/{visit:slug}', [VisitAuthorizationController::class, 'store'])
     ->middleware('auth', 'can:authorize,visit')
     ->name('visits.authorize.store');
Route::post('visits/attach-opd-card/{visit:slug}', [VisitAttachOPDCardController::class, 'store'])
     ->middleware('auth', 'can:attachOPDCard,visit')
     ->name('visits.attach-opd-card.store');

//evaluation
Route::patch('visits/{visit:slug}/evaluate', VisitEvaluateController::class)
     ->middleware('auth', 'can:evaluate')
     ->name('visits.evaluate');
Route::get('export/visits', VisitExportController::class)
     ->middleware('auth', 'can:evaluate')
     ->name('export.visits');
Route::post('import/colab', ImportColabController::class)
     ->middleware('auth', 'can:evaluate')
     ->name('import.colab');

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
Route::delete('visits/{visit:slug}', [VisitsController::class, 'destroy'])
     ->middleware('auth', 'can:cancel,visit')
     ->name('visits.cancel');
Route::get('visits/{visit:slug}/replace', [VisitsController::class, 'replace'])
     ->middleware('auth', 'can:replace,visit')
     ->name('visits.replace');

// print OPD card
Route::get('print-opd-card/{visit:slug}', PrintOPDCardController::class)
     ->middleware('auth', 'can:printOPDCard,visit')
     ->name('print-opd-card');

// resources
Route::middleware('auth')
     ->prefix('resources/api')
     ->name('resources.api.')
     ->group(function () {
         Route::get('patients/{hn}', ResourcePatientsController::class)
              ->name('patients.show');
         Route::get('employees/{id}', ResourceEmployeesController::class)
              ->name('employees.show');
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
