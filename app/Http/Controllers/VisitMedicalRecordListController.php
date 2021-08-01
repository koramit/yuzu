<?php

namespace App\Http\Controllers;

use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class VisitMedicalRecordListController extends Controller
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new VisitManager();
    }

    public function index()
    {
        $user = Auth::user();
        $today = today('asia/bangkok');
        $flash = $this->manager->getFlash($user);
        $flash['page-title'] = 'เวชระเบียน @ '.$today->format('d M Y');
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->whereDateVisit($today->format('Y-m-d'))
                       ->where(function ($query) {
                           $query->whereNotNull('enlisted_exam_at')
                                 ->orWhereNotNull('enlisted_swab_at')
                                 ->orWhere('status', 5); // cancled
                       })
                       ->orderBy('enlisted_screen_at')
                       ->get()
                       ->transform(function ($visit) use ($user) {
                           return [
                               'slug' => $visit->slug,
                               'hn' => $visit->hn ?? '',
                               'status' => $visit->status,
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'queued' => $visit->enqueued_at !== null,
                               'authorized' => $visit->authorized_at !== null,
                               'attached' => $visit->attached_opd_card_at !== null,
                               'enlisted_screen_at_for_humans' => $visit->enlisted_screen_at_for_humans,
                               'ready_to_print' => $visit->ready_to_print,
                               'can' => [
                                    'authorize_visit' => $user->can('authorize', $visit),
                                    'attach_opd_card' => $user->can('attachOPDCard', $visit),
                                    'print_opd_card' => $user->can('printOPDCard', $visit),
                                    'replace' => $user->can('replace', $visit),
                               ],
                           ];
                       });
        Session::put('back-from-show', 'visits.mr-list');

        return Inertia::render('Visits/List', [
            'visits' => $visits,
            'card' => 'mr',
            'eventSource' => 'mr',
        ]);
    }
}
