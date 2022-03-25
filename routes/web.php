<?php

use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CaptainMarvelController;
use App\Http\Controllers\CertificateListExportController;
use App\Http\Controllers\CertificationsController;
use App\Http\Controllers\CheckQueueSwabController;
use App\Http\Controllers\DutyTokensController;
use App\Http\Controllers\DutyTokenUserAuthorizationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportAppointmentsController;
use App\Http\Controllers\InTransitController;
use App\Http\Controllers\KotoController;
use App\Http\Controllers\LinkPatientController;
use App\Http\Controllers\MocktailController;
use App\Http\Controllers\MOPHVaccinationController;
use App\Http\Controllers\OPDCardExportController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PositiveCaseDecisionExportController;
use App\Http\Controllers\PreferencesController;
use App\Http\Controllers\PrintOPDCardController;
use App\Http\Controllers\RequestVerificationCodeController;
use App\Http\Controllers\ResourceEmployeesController;
use App\Http\Controllers\ResourcePatientsController;
use App\Http\Controllers\ServerSendEventsController;
use App\Http\Controllers\Services\LINEWebhooksController;
use App\Http\Controllers\SwapSwabController;
use App\Http\Controllers\UserMedicalRecordsController;
use App\Http\Controllers\VisitActionsController;
use App\Http\Controllers\VisitAttachOPDCardController;
use App\Http\Controllers\VisitAuthorizationController;
use App\Http\Controllers\VisitDecisionController;
use App\Http\Controllers\VisitDischargeListController;
use App\Http\Controllers\VisitEnqueueSwabListController;
use App\Http\Controllers\VisitEvaluateController;
use App\Http\Controllers\VisitExamListController;
use App\Http\Controllers\VisitExportController;
use App\Http\Controllers\VisitFillHnController;
use App\Http\Controllers\VisitLabListController;
use App\Http\Controllers\VisitMedicalRecordListController;
use App\Http\Controllers\VisitQueueListController;
use App\Http\Controllers\VisitsController;
use App\Http\Controllers\VisitScreenListController;
use App\Http\Controllers\VisitSwabListController;
use App\Http\Controllers\VisitSwabNotificationListController;
use App\Http\Controllers\VisitTodayListController;
use App\Http\Controllers\WonderWomenController;
use Illuminate\Support\Facades\Cache;
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
     ->name('logout');

// authorize duty token
Route::get('duty-token-user-authorization', [DutyTokenUserAuthorizationController::class, 'create'])
     ->middleware('auth')
     ->name('duty-token-user-authorization');
Route::post('duty-token-user-authorization', [DutyTokenUserAuthorizationController::class, 'store'])
     ->middleware('auth')
     ->name('duty-token-user-authorization.store');

// duty token
Route::get('active-duty-token', [DutyTokensController::class, 'show'])
     ->middleware('auth', 'can:view_active_duty_token')
     ->name('duty-token.show');
Route::post('active-duty-token', [DutyTokensController::class, 'store'])
     ->middleware('auth', 'can:create_active_duty_token')
     ->name('duty-token.store');

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
Route::get('tutorial', [PagesController::class, 'tutorial'])
     ->name('tutorial');

// preferences
Route::get('preferences', [PreferencesController::class, 'show'])
     ->middleware('auth')
     ->name('preferences');
Route::patch('preferences', [PreferencesController::class, 'update'])
     ->middleware('auth')
     ->name('preferences.update');

// home
Route::get('/', HomeController::class)
     ->middleware('auth')
     ->name('home');

// screen list
Route::get('visits/screen-list', [VisitScreenListController::class, 'index'])
     ->middleware('auth', 'authorize', 'can:view_screen_list')
     ->name('visits.screen-list');

// exam list
Route::get('visits/exam-list', [VisitExamListController::class, 'index'])
     ->middleware('auth', 'authorize', 'can:view_exam_list')
     ->name('visits.exam-list');
Route::patch('visits/exam-list/{visit:slug}', [VisitExamListController::class, 'store'])
     ->middleware('auth', 'authorize', 'can:update,visit')
     ->name('visits.exam-list.store');

// enqueue swab list
Route::get('visits/enqueue-swab-list', [VisitEnqueueSwabListController::class, 'index'])
     ->middleware('auth', 'authorize', 'can:view_enqueue_swab_list')
     ->name('visits.enqueue-swab-list');
Route::post('visits/enqueue-swab-list', [VisitEnqueueSwabListController::class, 'store'])
     ->middleware('auth', 'authorize', 'can:enqueue_swab')
     ->name('visits.enqueue-swab-list.store');
Route::patch('visits/enqueue-swab-list/{visit:slug}', [VisitEnqueueSwabListController::class, 'update']) // hold
     ->middleware('auth', 'authorize', 'can:enqueue_swab')
     ->name('visits.enqueue-swab-list.update');

// swab list
Route::get('visits/swab-list', [VisitSwabListController::class, 'index'])
     ->middleware('auth', 'authorize', 'can:view_swab_list')
     ->name('visits.swab-list');
Route::patch('visits/swab-list/{visit:slug}', [VisitSwabListController::class, 'store'])
     ->middleware('auth', 'authorize', 'can:update,visit')
     ->name('visits.swab-list.store');

// discharge from exam
Route::post('visits/discharge-list/{visit:slug}', [VisitDischargeListController::class, 'store'])
     ->middleware('auth', 'authorize', 'can:discharge,visit')
     ->name('visits.discharge-list.store');

// discharge from swab
Route::patch('visits/discharge-list/{visit:slug}', [VisitDischargeListController::class, 'update'])
     ->middleware('auth', 'authorize', 'can:discharge,visit')
     ->name('visits.discharge-list.update');

// medical record
Route::get('visits/mr-list', [VisitMedicalRecordListController::class, 'index'])
     ->middleware('auth', 'authorize', 'can:view_mr_list')
     ->name('visits.mr-list');
Route::post('visits/authorize/{visit:slug}', [VisitAuthorizationController::class, 'store'])
     ->middleware('auth', 'authorize', 'can:authorize,visit')
     ->name('visits.authorize.store');
Route::post('visits/attach-opd-card/{visit:slug}', [VisitAttachOPDCardController::class, 'store'])
     ->middleware('auth', 'authorize', 'can:attachOPDCard,visit')
     ->name('visits.attach-opd-card.store');

// today list
Route::get('visits/today-list', [VisitTodayListController::class, 'index'])
     ->middleware('auth', 'authorize', 'can:view_today_list')
     ->name('visits.today-list');

// lab list
Route::get('visits/lab-list', [VisitLabListController::class, 'index'])
     ->middleware('auth', 'authorize', 'can:view_any_visits')
     ->name('visits.lab-list');

// queue
Route::get('visits/queue-list', [VisitQueueListController::class, 'index'])
     ->middleware('auth', 'authorize', 'can:view_queue_list')
     ->name('visits.queue-list');
Route::post('visits/queue/{visit:slug}', [VisitQueueListController::class, 'store'])
     ->middleware('auth', 'authorize', 'can:queue,visit')
     ->name('visits.queue.store');
Route::post('visits/fill-hn/{visit:slug}', VisitFillHnController::class)
     ->middleware('auth', 'authorize', 'can:fillHn,visit')
     ->name('visits.fill-hn.store');

// view_swab_notification_list
Route::get('visits/swab-notification-list', [VisitSwabNotificationListController::class, 'index'])
     ->middleware('auth', 'can:view_swab_notification_list')
     ->name('visits.swab-notification-list');
Route::post('visits/swab-notification-list', [VisitSwabNotificationListController::class, 'store'])
     ->middleware('auth', 'can:notify_swab_queue')
     ->name('visits.swab-notification-list.store');

// swap wab
Route::patch('visits/{visit:slug}/swap-swab', SwapSwabController::class)
     ->middleware('auth', 'can:view_visit_actions')
     ->name('visits.swap-swab');

// evaluation
Route::patch('visits/{visit:slug}/evaluate', VisitEvaluateController::class) // save consultation note
     ->middleware('auth', 'can:evaluate')
     ->name('visits.evaluate');

// Decision
Route::get('decisions', [VisitDecisionController::class, 'index'])
     ->middleware('auth', 'remember', 'can:view_decision_list')
     ->name('decisions');
Route::patch('decisions/{visit:slug}', [VisitDecisionController::class, 'update'])
     ->middleware('auth', 'can:refer, visit')
     ->name('decisions.update');

// Certifications
Route::get('certifications', [CertificationsController::class, 'index'])
     ->middleware('auth', 'can:view_certification_list', 'remember')
     ->name('certifications');
Route::patch('certifications', [CertificationsController::class, 'update'])
     ->middleware('auth', 'can:certify')
     ->name('certifications.update');

// Export data
Route::get('export/opd_cards', OPDCardExportController::class)
     ->middleware('auth', 'can:export_opd_cards')
     ->name('export.opd_cards');
Route::get('export/visits', VisitExportController::class)
     ->middleware('auth', 'authorize', 'can:export_visits')
     ->name('export.visits');
Route::get('export/decisions', PositiveCaseDecisionExportController::class)
     ->middleware('auth', 'can:view_decision_list')
     ->name('export.decisions');
Route::get('export/certificates', CertificateListExportController::class)
     ->middleware('auth', 'can:view_certification_list')
     ->name('export.certificates');

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
Route::get('visits/{visit:slug}/transactions', [VisitActionsController::class, 'transactions'])
     ->middleware('auth', 'can:view_visit_actions') // can view transactions
     ->name('visits.transactions');

// appointments
Route::post('import/appointments', ImportAppointmentsController::class)
     ->middleware('auth', 'authorize', 'can:create_visit')
     ->name('import.appointments');
Route::post('appointments', AppointmentsController::class)
     ->middleware('auth', 'authorize', 'can:create_visit')
     ->name('appointments.store');

// print OPD card
Route::get('print-opd-card/{visit:slug}', PrintOPDCardController::class)
     ->middleware('auth', 'authorize', 'can:printOPDCard,visit')
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

// wonder woman
Route::get('ww', [WonderWomenController::class, 'index']);
Route::post('ww', [WonderWomenController::class, 'store']);
Route::patch('ww', [WonderWomenController::class, 'update']);
Route::get('croissant/feedback', [WonderWomenController::class, 'feedback']);
Route::get('croissant/{visit:slug}', [WonderWomenController::class, 'show'])
     ->middleware('auth')
     ->name('croissant');

// koto
Route::get('koto', [KotoController::class, 'index']);
Route::get('koto/feedback', [KotoController::class, 'update']);
Route::post('koto', [KotoController::class, 'store']);

// captain marvel
Route::get('cm', [CaptainMarvelController::class, 'index']);
Route::get('cm-clear', function () {
    return \Cache::pull('today-lab-patients', []);
});

// link mocktail
Route::post('mocktail', MocktailController::class)
     ->middleware('auth', 'can:link_mocktail')
     ->name('mocktail.link');

// LINE notify
Route::post('webhook/line', LINEWebhooksController::class);

// Verification
Route::post('request-verification-code', RequestVerificationCodeController::class)
     ->middleware('auth')
     ->name('request-verification-code');

// link HN
Route::post('link-patient', LinkPatientController::class)
     ->middleware('auth')
     ->name('link-patient');

// in-transit
Route::get('in-transit', InTransitController::class)
     ->middleware('auth', 'can:in_transit')
     ->name('in-transit');

// medical-records
Route::get('medical-records', UserMedicalRecordsController::class)
     ->middleware('auth')
     ->name('medical-records');

// check queue swab
Route::get('check-queue-swab', [CheckQueueSwabController::class, 'create'])
     ->name('check-queue-swab');
Route::post('check-queue-swab', [CheckQueueSwabController::class, 'show'])
     ->name('check-queue-swab.show');

Route::post('moph-vaccination/{cip}', [MOPHVaccinationController::class, 'show'])
     ->middleware('auth')
     ->name('moph-vaccination');

// SiIT feedback
Route::get('siit-feedback', function () {
    $log = Cache::get('siit-log', []);
    $reversed = [];
    foreach (array_reverse(array_keys($log)) as $key) {
        $reversed[$key] = $log[$key];
    }
    return $reversed;
});

Route::get('siit-cert-feedback', function () {
    $log = Cache::get('siit-cert-log', []);
    $reversed = [];
    foreach (array_reverse(array_keys($log)) as $key) {
        $reversed[$key] = $log[$key];
    }
    return $reversed;
});

Route::get('user-add-line', function () {
    return \App\Models\User::whereNotNull('profile->notification')
                              ->get()
                              ->transform(function ($u) {
                                  return [
                                        'nickname' => $u->profile['notification']['nickname'] ?? null,
                                        'avatar' => $u->profile['notification']['avatar'] ?? null,
                                        'status' => $u->profile['notification']['status'] ?? null,
                                        'active' => $u->profile['notification']['active'] ?? null,
                                        'patient_linked' => $u->profile['patient_id'] ?? null
                                   ];
                              });
})->middleware('auth', 'can:notify_swab_queue');

Route::get('nephro-fellow-2023', function () {
    return response()->file(public_path('/download/nephro-fellow-2023.zip'));
});

/*
 * Route for testing ONLY
 */
Route::get('login-as/{name}', function ($name) {
    if (config('app.env') === 'production') {
        abort(404);
    }
    $user = \App\Models\User::whereName($name)->first();
    if (! $user) {
        abort(404);
    }
    \Auth::login($user);

    $redirectTo = $user->home_page;
    if ($user->role_names->count() && !$user->role_names->intersect(config('app.specific_roles'))->count()) {
        $redirectTo = 'in-transit';
    }

    return redirect()->route($redirectTo);
});

Route::post('transfer', function () {
    if (config('app.env') === 'production') {
        abort(404);
    }

    $transfer = new App\Transfer();
    return $transfer->set(request()->all());
});
