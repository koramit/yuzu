<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VisitQueueListController extends Controller
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new VisitManager();
    }

    public function index()
    {
        $user = Auth::user();
        $today = now('asia/bangkok');
        $flash = $this->manager->getFlash($user);
        $flash['page-title'] = 'ธุรการ @ '.$today->format('d M Y');
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->whereDateVisit($today->format('Y-m-d'))
                       ->where(function ($query) {
                           $query->whereNotNull('enlisted_exam_at')
                                 ->orWhereNotNull('enlisted_swab_at');
                       })
                       ->where(function ($query) {
                           $query->whereNull('enqueued_at')
                                 ->orWhere('patient_id', null);
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
                               'enlisted_screen_at_for_humans' => $visit->enlisted_screen_at_for_humans,
                               'ready_to_print' => $visit->ready_to_print,
                               'swab' => $visit->form['management']['np_swab'],
                               'swab_at' => $visit->container_swab_at ?? $visit->swab_at ?? '',
                               'track' => $visit->track ?? '',
                               'mobility' => $visit->mobility ?? '',
                               'can' => [
                                    'queue' => $user->can('queue', $visit),
                                    'fill_hn' => $user->can('fillHn', $visit),
                               ],
                           ];
                       });

        return Inertia::render('Visits/List', [
            'visits' => $visits,
            'card' => 'queue',
            'eventSource' => 'mr',
        ]);
    }

    public function store(Visit $visit)
    {
        $visit->enqueued_at = now();
        $visit->save();
        $visit->actions()->create([
            'action' => 'enqueue',
            'visit_id' => $visit->id,
            'user_id' => Auth::id(),
        ]);
        VisitUpdated::dispatch($visit);

        return redirect()->back();
    }
}
