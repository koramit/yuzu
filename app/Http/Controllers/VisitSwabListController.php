<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
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
        $today = now()->today('asia/bangkok');
        $flash = $this->manager->getFlash($user);
        $flash['page-title'] = 'ห้อง Swab @ '.$today->format('d M Y');
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->whereDateVisit($today)
                       ->whereStatus(3)
                       ->orderBy('enlisted_swab_at')
                       ->get()
                       ->transform(function ($visit) use ($user) {
                           return [
                               'slug' => $visit->slug,
                               'hn' => $visit->hn,
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'enlisted_swab_at_for_humans' => $visit->enlisted_swab_at_for_humans,
                               'can' => [
                                'discharge' => $user->can('discharge', $visit),
                               ],
                           ];
                       });

        return Inertia::render('Visits/List', [
            'visits' => $visits,
            'card' => 'swab',
        ]);
    }

    public function store(Visit $visit)
    {
        $data = Request::all();

        $manager = new VisitManager();
        $user = Auth::user();
        if ($user->role_names->contains('md')) {
            $errors = $manager->validateSwabByMD($data);
        } elseif ($user->role_names->contains('nurse')) {
            $errors = $manager->validateSwabByNurse($data);
        }
        $manager->saveVisit($visit, $data, $user);

        if (count($errors)) {
            return back()->withErrors($errors);
        }

        // status
        $route = $visit->status_index_route;
        $visit->status = 'swab';
        $visit->enlisted_swab_at = now();
        if ($user->role_names->contains('md')) {
            $visit->forceFill([
                'form->md->name' => $user->profile['full_name'],
                'form->md->pln' => $user->profile['pln'],
                'form->md->signed_on_behalf' => false,
                'form->md->signed_at' => now(),
            ]);
        } else {
            $visit->forceFill([
                'form->md' => $manager->getIdStaff($data['md_name']) + ['signed_on_behalf' => true, 'signed_at' => now()],
            ]);
        }
        $visit->save();

        $visit->actions()->createMany([
            ['action' => 'sign', 'user_id' => $user->id],
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
