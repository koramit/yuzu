<?php

namespace App\Http\Controllers;

use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VisitEvaluationListController extends Controller
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new VisitManager();
    }

    public function index()
    {
        $flash = $this->manager->getFlash(Auth::user());
        $flash['page-title'] = 'ตรวจ';
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->whereDateVisit(now()->today('asia/bangkok'))
                       ->whereStatus(2)
                       ->orderByDesc('updated_at')
                       ->paginate()
                       ->through(function ($visit) {
                           return [
                               'slug' => $visit->slug,
                               'hn' => $visit->patient->hn ?? null,
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'date_visit' => $visit->date_visit->format('d M Y'),
                               'updated_at_for_humans' => $visit->updated_at_for_humans,
                           ];
                       });

        return Inertia::render('Visits/Index', ['visits' => $visits]);
    }
}
