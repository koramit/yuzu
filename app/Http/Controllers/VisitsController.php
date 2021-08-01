<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Managers\PatientManager;
use App\Managers\VisitManager;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
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
        $user = Auth::user();
        $flash = $this->manager->getFlash($user);
        $flash['page-title'] = 'รายการเคส';
        $this->manager->setFlash($flash);

        $visits = Visit::with('patient')
                       ->orderByDesc('date_visit')
                       ->orderByDesc('updated_at')
                       ->paginate()
                       ->through(function ($visit) use ($user) {
                           return [
                               'slug' => $visit->slug,
                               'hn' => $visit->hn,
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'date_visit' => $visit->date_visit->format('d M Y'),
                               'updated_at_for_humans' => $visit->updated_at_for_humans,
                               'can' => [
                                   'view' => $user->can('view', $visit),
                               ],
                           ];
                       });
        Session::put('back-from-show', 'visits');

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
                return $this->handleDuplicate($visit, $user);
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
                return $this->handleDuplicate($visit, $user);
            }
            $visit = new Visit();
            $form['patient']['name'] = $data['patient_name'];
        }

        $visit->slug = Str::uuid()->toString();
        $visit->date_visit = $todayStr;
        $visit->form = $form;
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
        // update appointment to screen
        if ($visit->date_visit->format('Ymd') === today('asia/bangkok')->format('Ymd')) {
            $visit->status = 'screen';
            $visit->enlisted_screen_at = now();
            $visit->save();
            $visit->actions()->create(['action' => 'enlist_screen', 'user_id' => $user->id]);
        }
        if ($visit->status !== 'appointment') {
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

    public function show(Visit $visit)
    {
        $flash['page-title'] = $visit->title;
        $flash['main-menu-links'] = [
            ['icon' => 'arrow-circle-left', 'label' => 'ย้อนกลับ', 'route' => Session::get('back-from-show', 'visits.mr-list'), 'can' => true],
        ];
        $flash['action-menu'] = [];
        $this->manager->setFlash($flash);

        $user = Auth::user();
        $visit->actions()->create(['action' => 'view', 'user_id' => $user->id]);

        return Inertia::render('Visits/Show', [
            'content' => $this->manager->getReportContent($visit),
            'configs' => [
                'topics' => ['ประวัติเสี่ยง', 'โรคประจำตัว', 'ประวัติการฉีดวัคซีน COVID-19', 'วินิจฉัย', 'การจัดการ', 'คำแนะนำสำหรับผู้ป่วย', 'note'],
            ],
            'can' => [
                'evaluate' => $user->can('evaluate'),
                'print_opd_card' => $user->can('printOpdCard', $visit),
            ],
        ]);
    }

    public function replace(Visit $visit)
    {
        // it actually is unlocking visit to updatable

        // reset discharged_at & enlisted_swab_at (ready_to_print = false)
        $visit->discharged_at = null;
        $visit->enlisted_swab_at = null;
        // reset attached_opd_card_at
        $visit->attached_opd_card_at = null;
        // if unlock by md set status to exam and update enlisted_exam_at if needed
        // if unlock by nurse set status to screen
        $user = Auth::user();
        if ($user->hasRole('md')) {
            $visit->status = 'exam';
            if (! $visit->enlisted_exam_at) {
                $visit->enlisted_exam_at = now();
            }
        } elseif ($user->hasRole('nurse')) {
            $visit->status = 'screen';
        }
        $visit->save();
        // backup version
        $visit->versions()->create(['form' => $visit->form, 'user_id' => $user->id]);
        // log action
        $visit->actions()->create(['action' => 'unlock', 'user_id' => $user->id]);
        // dispatch event
        VisitUpdated::dispatch($visit);
        // redirect to edit
        return Redirect::route('visits.edit', $visit);
    }

    public function destroy(Visit $visit)
    {
        $visit->forceFill([
            'status' => 'canceled',
            'form->cancel_reason' => Request::input('reason'),
        ]);
        $visit->save();
        $visit->actions()->create([
            'action' => 'cancel',
            'user_id' => Auth::id(),
        ]);
        VisitUpdated::dispatch($visit);

        return Redirect::back();
    }

    protected function handleDuplicate(Visit $visit, User $user)
    {
        if (
            ($user->hasRole('nurse') && ($visit->status === 'screen')) ||
            (($user->hasRole('md')) && ($visit->status === 'exam'))
        ) {
            return Redirect::route('visits.edit', $visit);
        } elseif (collect(['swab', 'discharged'])->contains($visit->status)) {
            return Redirect::route('visits.show', $visit);
        } elseif ($visit->status === 'canceled') {
            $visit->enqueued_at = null;
            $visit->attached_opd_card_at = null;
            $visit->authorized_at = null;
            if ($user->hasRole('nurse')) {
                $visit->status = 'screen';
            } elseif ($user->hasRole('md')) {
                $visit->enlisted_exam_at = $visit->enlisted_exam_at ?? now();
                $visit->status = 'exam';
            }
            $visit->save();
            $visit->actions()->create(['action' => 'recreate', 'user_id' => $user->id]);
            VisitUpdated::dispatch($visit);

            return Redirect::route('visits.edit', $visit);
        }
    }
}
