<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class VisitExamListController extends Controller
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
        $flash['page-title'] = 'ห้องตรวจ @ '.$today->format('d M Y');
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->whereNotNull('patient_id')
                       ->whereNotNull('authorized_at')
                       ->whereDateVisit($today)
                       ->whereStatus(2)
                       ->orderBy('enlisted_exam_at')
                       ->get()
                       ->transform(function ($visit) use ($user) {
                           return [
                                'slug' => $visit->slug,
                                'title' => $visit->title,
                                'hn' => $visit->hn,
                                'patient_name' => $visit->patient_name,
                                'patient_type' => $visit->patient_type,
                                'enlisted_exam_at_for_humans' => $visit->enlisted_exam_at_for_humans,
                                'can' => [
                                    'update' => $user->can('update', $visit),
                                    'cancel' => $user->can('cancel', $visit),
                                ],
                           ];
                       });

        return Inertia::render('Visits/List', [
            'visits' => $visits,
            'card' => 'exam',
            'eventSource' => 'exam',
        ]);
    }

    public function store(Visit $visit)
    {
        $data = Request::all();
        $user = Auth::user();

        $errors = $this->manager->validateScreening($data);

        (new VisitManager())->saveVisit($visit, $data, $user);

        if (count($errors)) {
            return back()->withErrors($errors);
        }

        // status
        $route = $visit->status_index_route;
        $visit->status = 'exam';
        $visit->enlisted_exam_at = now();
        if ($visit->patient_type === 'เจ้าหน้าที่ศิริราช' && $visit->patient_id) {
            $visit->enqueued_at = now();
        }
        $visit->save();

        $visit->actions()->create(['action' => 'enlist_exam', 'user_id' => $user->id]);

        VisitUpdated::dispatch($visit);

        return Redirect::route($route)->with('messages', [
            'status' => 'success',
            'messages' => [
                $visit->title.' ส่งตรวจสำเร็จ',
            ],
        ]);
    }
}
