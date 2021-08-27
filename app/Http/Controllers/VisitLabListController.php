<?php

namespace App\Http\Controllers;

use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class VisitLabListController extends Controller
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new VisitManager();
    }

    public function index()
    {
        $user = Auth::user();
        $today = now();
        $flash = $this->manager->getFlash($user);
        $flash['page-title'] = 'ถ่ายถอดสด @ '.$today->format('d M Y');
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->whereDateVisit($today->format('Y-m-d'))
                       ->where('swabbed', true)
                       ->orderByDesc('updated_at')
                       ->get()
                       ->transform(function ($visit) use ($user) {
                           return [
                               'slug' => $visit->slug,
                               'hn' => $visit->hn ?? '',
                               'status' => $visit->status,
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'result' => $visit->form['management']['np_swab_result'] ?? '',
                               'note' => $visit->form['management']['np_swab_result_note'] ?? '',
                               'screenshot' => $visit->form['management']['screenshot'] ?? null,
                               'updated_at_for_humans' => $visit->updated_at_for_humans,
                           ];
                       });
        Session::put('back-from-show', 'visits.lab-list');

        return Inertia::render('Visits/List', [
            'visits' => $visits,
            'card' => 'lab',
            'eventSource' => 'mr',
        ]);
    }
}
