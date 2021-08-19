<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class VisitSwabListController extends Controller
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
        $flash['page-title'] = 'ห้อง Swab @ '.$today->format('d M Y');
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->whereDateVisit($today->format('Y-m-d'))
                       ->whereStatus(7) // enqueue_swab
                       ->orderBy('enqueued_swab_at')
                       ->orderBy('enlisted_swab_at')
                       ->get()
                       ->transform(function ($visit) use ($user) {
                           return [
                               'id' => $visit->id,
                               'slug' => $visit->slug,
                               'hn' => $visit->hn,
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'enlisted_swab_at_for_humans' => $visit->enlisted_swab_at_for_humans,
                               'swab_at' => $visit->container_swab_at,
                               'specimen_no' => $visit->specimen_no,
                               'container_no' => $visit->container_no,
                               'queued' => $visit->enqueued_at !== null,
                               'authorized' => $visit->authorized_at !== null,
                               'attached' => $visit->attached_opd_card_at !== null,
                               'ready_to_print' => $visit->ready_to_print,
                               'on_hold' => $visit->form['management']['on_hold'] ?? false,
                               'busy' => false,
                               'can' => [
                                    'discharge' => $user->can('discharge', $visit),
                                    'hold' => $user->can('enqueue_swab'),
                                    'authorize_visit' => $user->can('authorize', $visit),
                                    'attach_opd_card' => $user->can('attachOPDCard', $visit),
                                    'print_opd_card' => $user->can('printOPDCard', $visit),
                               ],
                           ];
                       });

        return Inertia::render('Visits/List', [
            'visits' => $visits,
            'card' => 'swab',
            'eventSource' => 'mr',
        ]);
    }

    public function store(Visit $visit)
    {
        $data = Request::all();

        $manager = new VisitManager();
        $user = Auth::user();
        if ($user->hasRole('md')) {
            $errors = $manager->validateSwabByMD($data);
        } elseif ($user->hasRole('nurse')) {
            $errors = $manager->validateSwabByNurse($data);
        }
        $manager->saveVisit($visit, $data, $user);

        if (count($errors)) {
            return back()->withErrors($errors);
        }

        // status
        $visit->status = 'swab';
        $visit->enlisted_swab_at = now();
        if ($user->hasRole('md')) {
            $visit->forceFill([
                'form->md' => [
                    'name' => $user->profile['full_name'],
                    'pln' => $user->profile['pln'],
                    'signed_on_behalf' => false,
                    'signed_at' => now(),
                ],
            ]);
            $route = 'visits.exam-list';
        } elseif ($user->hasRole('nurse')) {
            $visit->forceFill([
                'form->md' => $manager->getIdStaff($data['md_name']) + ['signed_on_behalf' => true, 'signed_at' => now()],
            ]);
            $route = 'visits.screen-list';
        }
        if ($visit->patient_type === 'เจ้าหน้าที่ศิริราช' && $visit->patient_id) {
            $visit->enqueued_at = now();
        }
        // running specimen no
        if (! ($visit->form['management']['specimen_no'])) {
            $cacheName = now('asia/bangkok')->format('Y-m-d').'-specimen-running-no';
            $visit->forceFill([
                'form->management->specimen_no' => Cache::increment($cacheName),
            ]);
        }

        // check if edit after enqueued swab
        if ($visit->container_no) {
            $visit->status = 'enqueue_swab';
        }

        $visit->save();

        $visit->actions()->createMany([
            ['action' => 'sign_opd_card', 'user_id' => $user->id],
            ['action' => 'enlist_swab', 'user_id' => $user->id],
        ]);

        VisitUpdated::dispatch($visit);

        return Redirect::route($route)->with('messages', [
            'status' => 'success',
            'messages' => [
                $visit->title.' ส่ง swab สำเร็จ',
            ],
        ]);
    }
}
