<?php

namespace App\Http\Controllers;

use App\Managers\MocktailManager;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class VisitDecisionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $manager = new VisitManager();
        $flash = $manager->getFlash($user);
        $flash['page-title'] = 'Decision';
        $manager->setFlash($flash);
        $manager = new MocktailManager();
        $dateVisit = Request::input('date_visit', now('asia/bangkok')->format('Y-m-d'));
        $positiveCases = Visit::with('patient')
                              ->where('swabbed', true)
                              ->whereDateVisit($dateVisit)
                              ->where('form->management->np_swab_result', 'Detected')
                              ->get()
                              ->transform(function ($visit) use ($manager) {
                                  return $manager->getReferCase($visit);
                              });

        return Inertia::render('Decisions/Index', [
            'positiveCases' => $positiveCases,
            'dateVisit' => $dateVisit,
            'referToOptions' => ['Ward', 'Baiyoke', 'Riverside', 'HI', 'Colink', 'อื่นๆ'],
        ]);
    }
}
