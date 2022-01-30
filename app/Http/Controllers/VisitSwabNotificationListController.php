<?php

namespace App\Http\Controllers;

use App\Managers\NotificationManager;
use App\Managers\VisitManager;
use App\Models\Visit;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
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

        $visits = Visit::with(['patient' => fn ($query) => $query->withTodaySwabNotifications()])
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
                               'notification_user_id' => $visit->patient->notification_user_id,
                               'notification_active' => $visit->patient->notification_active,
                               'notification_count' => $visit->patient->chatLogs->count(),
                               'calling' => false,
                               'id' => $visit->id,
                           ];
                       });

        return Inertia::render('Visits/List', [
            'visits' => $visits,
            'card' => 'swab-notification',
            'eventSource' => 'mr',
        ]);
    }

    public function store()
    {
        Request::validate([
            'userIds' => 'required|array',
        ]);

        $manager = new NotificationManager();
        $errors = [];

        foreach (Request::input('userIds') as $id) {
            try {
                $manager->notifySwabQueue(userId: $id);
            } catch (Exception $e) {
                Log::error($e->getMessage());
                Log::error('notify-swab-queue@'.$id);
                $errors[] = $id;
            }
        }

        return [
            'ok' => true,
            'errors' => $errors,
        ];
    }
}
