<?php

namespace App\Http\Controllers;

use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VisitScreenListController extends Controller
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
        $flash['page-title'] = 'ห้องคัดกรอง @ '.$today->format('d M Y');
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->where(function ($query) use ($today) {
                           $query->orWhere('date_visit', $today)
                                  ->orWhere('date_visit', $today->addDays(1));
                       })
                    //    ->whereBetween('date_visit', [$today->addDays(-1), $today->addDays(1)])
                       ->whereIn('status', [1, 6]) // screen, appointment
                       ->orderBy('enlisted_screen_at')
                       ->get()
                       ->transform(function ($visit) use ($user) {
                           return [
                               'slug' => $visit->slug,
                               'title' => $visit->title,
                               'hn' => $visit->hn ?? '',
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'enlisted_screen_at_for_humans' => $visit->enlisted_screen_at_for_humans,
                               'can' => [
                                    'update' => $user->can('update', $visit),
                                    'cancel' => $user->can('cancel', $visit),
                                ],
                           ];
                       });

        return Inertia::render('Visits/List', [
            'visits' => $visits,
            'card' => 'screen',
        ]);
    }
}
