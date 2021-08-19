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

class VisitEnqueueSwabListController extends Controller
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
        $flash['page-title'] = 'จัดกระติก @ '.$today->format('d M Y');
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->whereDateVisit($today->format('Y-m-d'))
                       ->whereStatus(3) // swab
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
                               'selected' => false,
                               'id' => $visit->id,
                           ];
                       });

        return Inertia::render('Visits/List', [
            'visits' => $visits,
            'card' => 'enqueue-swab',
            'eventSource' => 'mr',
        ]);
    }

    public function store()
    {
        $cacheName = now('asia/bangkok')->format('Y-m-d').'-container-running-no';
        if (Request::input('move')) {
            $data = [
                'form->management->container_swab_at' => Request::input('swab_at'),
            ];
        } else {
            $data = [
                'enqueued_swab_at' => now(),
                'status' => 7, // enqueue_swab,
                'form->management->container_no' => Cache::increment($cacheName),
                'form->management->container_swab_at' => Request::input('swab_at'),
            ];
        }
        Visit::unguard();
        Visit::whereIn('id', Request::input('ids'))
             ->update($data);
        Visit::reguard();
        $visit = Visit::find(Request::input('ids')[0]);
        VisitUpdated::dispatch($visit);

        return Redirect::back();
    }

    public function update(Visit $visit)
    {
        $containerNo = null;
        if (Request::has('container_no')) {
            $no = Request::input('container_no');
            if ($no === 'new') {
                $cacheName = now('asia/bangkok')->format('Y-m-d').'-container-running-no';
                $containerNo = Cache::increment($cacheName);
            } elseif (is_numeric($no)) {
                $containerNo = $no;
            } else {
                $visit->forceFill(['form->management->container_swab_at' => $no])->save();
                VisitUpdated::dispatch($visit);

                return [
                    'ok' => true,
                    'move_to' => $no,
                ];
            }
            $visit->forceFill([
                'form->management->on_hold' => false,
                'form->management->container_no' => $containerNo,
            ])->save();
        } else {
            $visit->forceFill([
                'form->management->on_hold' => true,
            ])->save();
        }

        return [
            'ok' => true,
            'container_no' => $containerNo,
        ];
    }
}
