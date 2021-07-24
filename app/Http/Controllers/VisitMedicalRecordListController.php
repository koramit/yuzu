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
        $today = now()->today('asia/bangkok');
        $flash = $this->manager->getFlash($user);
        $flash['page-title'] = 'เวชระเบียน @ '.$today->format('d M Y');
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->whereDateVisit($today)
                       ->orderBy('enlisted_screen_at')
                       ->get()
                       ->transform(function ($visit) use ($user) {
                           return [
                               'slug' => $visit->slug,
                               'hn' => $visit->patient->hn ?? null,
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'authorized' => $visit->authorized_at ? true : false,
                               'attached' => $visit->attached_opd_card_at ? true : false,
                               'enlisted_screen_at_for_humans' => $visit->enlisted_screen_at_for_humans,
                               'ready_to_print' => $visit->ready_to_print,
                               'can' => [
                                    'authorize_visit' => $user->can('authorize_visit'),
                                    'attach_opd_card' => $user->can('attach_opd_card'),
                                    'print_opd_card' => $user->can('print_opd_card'),
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
