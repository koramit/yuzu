<?php

namespace App\Http\Controllers;

use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VisitSwabNotificationListController extends Controller
{
    public function __construct()
    {
        $this->manager = new VisitManager();
    }

    public function index()
    {
        $user = Auth::user();
        $today = now('asia/bangkok');
        $flash = $this->manager->getFlash($user);
        $flash['page-title'] = 'เรียกคิว Swab @ '.$today->format('d M Y');
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->whereDateVisit($today->format('Y-m-d'))
                       ->whereIn('status', [3, 7]) // swab and enqueue_swab
                       ->orderBy('enlisted_swab_at')
                       ->get()
                       ->transform(function ($visit) use ($user) {
                           return [
                               'slug' => $visit->slug,
                               'hn' => $visit->hn,
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'enlisted_swab_at_for_humans' => $visit->enlisted_swab_at_for_humans,
                               'swab_at' => $visit->swab_at,
                               'specimen_no' => $visit->specimen_no,
                               'notification_active' => $visit->patient->notification_active,
                               'id' => $visit->id,
                           ];
                       });

        return Inertia::render('Visits/List', [
            'visits' => $visits,
            'card' => 'swab-notification',
            'eventSource' => 'mr',
        ]);
    }
}
