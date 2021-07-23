<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Managers\PatientManager;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VisitsController extends Controller
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new VisitManager();
    }

    public function index()
    {
        $flash = $this->manager->getFlash(Auth::user());
        $flash['page-title'] = 'รายการเคส';
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->orderByDesc('updated_at')
                       ->paginate()
                       ->through(function ($visit) {
                           return [
                               'slug' => $visit->slug,
                               'hn' => $visit->patient->hn ?? null,
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'date_visit' => $visit->date_visit->format('d M Y'),
                               'updated_at_for_humans' => $visit->updated_at_for_humans,
                           ];
                       });

        return Inertia::render('Visits/Index', ['visits' => $visits]);
    }

    public function store()
    {
        $data = Request::all();
        $user = Auth::user();

        $todayStr = now($user->timezone)->format('Y-m-d');

        $form = $this->manager->initForm();

        if ($data['hn']) {
            $patient = (new PatientManager())->manage($data['hn']);
            /* SHOULD BE FOUND */
            if (! $patient['found']) {
                return '🥺';
            }
            $visit = Visit::whereDateVisit($todayStr)
                          ->wherePatientId($patient['patient']->id)
                          ->first();
            if ($visit) {
                return Redirect::route('visits.edit', $visit);
            }
            $visit = new Visit();
            $visit->patient_id = $patient['patient']->id;
            $form['patient']['hn'] = $patient['patient']->hn;
            $form['patient']['name'] = $patient['patient']->full_name;
            $form['patient']['tel_no'] = $patient['patient']->profile['tel_no'];
        } else {
            $visit = Visit::whereDateVisit($todayStr)
                          ->where('form->patient->name', $data['patient_name'])
                          ->first();
            if ($visit) {
                return Redirect::route('visits.edit', $visit);
            }
            $visit = new Visit();

            $form['patient']['name'] = $data['patient_name'];
        }

        $visit->slug = Str::uuid()->toString();
        $visit->date_visit = $todayStr;
        $visit->form = $form;
        $visit->creator_id = $user->id;
        // $visit->updater_id = $visit->creator_id;
        // ** auto enlisted_screen_at for now
        $visit->status = 'screen';
        $visit->enlisted_screen_at = now();
        $visit->save();

        $visit->actions()->createMany([
            ['action' => 'create', 'user_id' => $user->id],
            ['action' => 'enlist_screen', 'user_id' => $user->id],
        ]);

        VisitUpdated::dispatch($visit);

        return Redirect::route('visits.edit', $visit);
    }

    public function edit(Visit $visit)
    {
        $flash['page-title'] = $visit->title;
        $flash['main-menu-links'] = [
            ['icon' => 'arrow-circle-left', 'label' => 'ย้อนกลับ', 'route' => $visit->status_index_route, 'can' => true],
        ];
        $flash['action-menu'] = [];
        $flash['messages'] = [
            'status' => 'info',
            'messages' => [
                'หากยังไม่ส่งผู้ป่วยไปขั้นตอนอื่น โปรดทำการ <span class="font-semibold">บันทึก</span> ทุกครั้งก่อนออกจากฟอร์ม',
            ],
        ];

        $user = Auth::user();
        $can = [];
        if ($user->can('update', $visit)) { // save only
            $flash['action-menu'][] = ['icon' => 'save', 'label' => 'บันทึก', 'action' => 'save', 'can' => true];
            $can[] = 'save';
        }
        if ($user->can('enlist_exam')) { // save to exam -- NURSE only
            $flash['action-menu'][] = ['icon' => 'share-square', 'label' => 'ส่งตรวจ', 'action' => 'save-exam', 'can' => true];
            $can[] = 'save-exam';
        }
        if ($user->can('sign_opd_card')) { // save to discharge -- MD only
            $flash['action-menu'][] = ['icon' => 'share-square', 'label' => 'จำหน่าย', 'action' => 'save-discharge', 'can' => true];
            $can[] = 'save-discharge';
        }
        if ($user->role_names->contains('nurse')) { // NURSE save to swab
            if ($visit->screen_type && $visit->screen_type !== 'เริ่มตรวจใหม่') {
                $flash['action-menu'][] = ['icon' => 'share-square', 'label' => 'ส่ง swab', 'action' => 'save-swab', 'can' => true];
            }
        } elseif ($user->role_names->contains('md')) { // MD save to swab
            if ($visit->form['management']['np_swab']) {
                $flash['action-menu'][] = ['icon' => 'share-square', 'label' => 'ส่ง swab', 'action' => 'save-swab', 'can' => true];
            }
        }

        $this->manager->setFlash($flash);

        if ($visit->patient) {
            $visit->patient_document_id = $visit->patient->profile['document_id'];
            $visit->has_patient = true;
            unset($visit->patient);
        } else {
            $visit->has_patient = false;
        }

        $configs = $this->manager->getConfigs($visit);
        $configs['can'] = $can;

        return Inertia::render('Visits/Edit', [
            'visit' => $visit,
            'formConfigs' => $configs,
        ]);
    }

    public function update(Visit $visit)
    {
        (new VisitManager())->saveVisit($visit, Request::all(), Auth::user());

        return Redirect::route($visit->status_index_route)->with('messages', [
            'status' => 'success',
            'messages' => [
                'บันทึก '.$visit->title.' สำเร็จ',
            ],
        ]);
    }
}
