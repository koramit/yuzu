<?php

namespace App\Http\Controllers;

use App\Managers\MocktailManager;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class VisitDicisionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $manager = new VisitManager();
        $flash = $manager->getFlash($user);
        $flash['page-title'] = 'Dicision';
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

        return Inertia::render('Dicisions/Index', [
            'positiveCases' => $positiveCases,
            'dateVisit' => $dateVisit,
        ]);
    }
}
