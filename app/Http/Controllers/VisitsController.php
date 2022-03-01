<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Managers\PatientManager;
use App\Managers\VisitManager;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
                       ->whereNotNull('patient_id')
                       ->whereIn('status', [4,5])
                       ->filter(Request::only('search'))
                       ->orderByDesc('date_visit')
                       ->orderByDesc('updated_at')
                       ->paginate()
                       ->withQueryString()
                       ->through(function ($visit) use ($user) {
                           return [
                               'slug' => $visit->slug,
                               'hn' => $visit->hn,
                               'swabbed' => $visit->swabbed,
                               'swab' => $visit->form['management']['np_swab'],
                               'patient_name' => $visit->patient_name,
                               'patient_type' => $visit->patient_type,
                               'date_visit' => $visit->date_visit->format('d M Y'),
                               'updated_at_for_humans' => $visit->updated_at_for_humans,
                               'result' => $visit->form['management']['np_swab_result'] ?? 'Pending',
                               'note' => $visit->form['management']['np_swab_result_note'],
                               'can' => [
                                   'view' => $user->can('view', $visit),
                                   'view_visit_actions' => $user->can('view_visit_actions'),
                               ],
                           ];
                       });
        Session::put('back-from-show', 'visits');

        return Inertia::render('Visits/Index', [
            'visits' => $visits,
            'filters' => Request::all('search'),
            'can' => [
                'export_opd_cards' => $user->can('export_opd_cards'),
            ],
        ]);
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
                Log::error('visit@store validate '.$data['hn'].' not found. last search '.Session::get('last-search-hn'));

                return 'เกิดข้อผิดพลาด กรุณาลองใหม่ ขออภัยในความไม่สะดวก 🥺';
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
            try {
                $tel = str_replace('-', '', $patient['patient']->profile['tel_no']);
                $tel = str_replace(',', ' ', $tel);
                $telNos = explode(' ', $tel);
                $telNos[0] = trim($telNos[0]);
                $form['patient']['tel_no'] = str_starts_with($telNos[0], '02') ? null : $telNos[0];
            } catch (\Exception $e) {
                Log::error($patient['patient']->profile['tel_no'].' '.$e->getMessage());
                $form['patient']['tel_no'] = $patient['patient']->profile['tel_no'];
            }
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
        if ($visit->status === 'appointment' && $visit->date_visit->format('Ymd') === now('asia/bangkok')->format('Ymd')) {
            $visit->status = 'screen';
            $visit->enlisted_screen_at = now();
            $visit->save();
            $visit->actions()->create(['action' => 'enlist_screen', 'user_id' => $user->id]);
        }
        if ($visit->status !== 'appointment') {
            if ($user->can('enlist_exam') && ! $visit->swabbed) { // save to exam -- NURSE only
                $flash['action-menu'][] = ['icon' => 'share-square', 'label' => 'ส่งพบแพทย์ตรวจ', 'action' => 'save-exam', 'can' => true];
                $can[] = 'save-exam';
            }
            if ($user->can('discharge', $visit)) { // save to discharge -- MD only
                $can[] = 'save-discharge';
                if (! $visit->form['management']['np_swab']) {
                    $flash['action-menu'][] = ['icon' => 'share-square', 'label' => 'จำหน่ายไม่ต้อง swab', 'action' => 'save-discharge', 'can' => true];
                }
            }
            if ($user->role_names->contains('nurse')) { // NURSE save to swab
                if ($visit->screen_type && $visit->screen_type !== 'เริ่มตรวจใหม่' && ! $visit->swabbed) {
                    $flash['action-menu'][] = ['icon' => 'share-square', 'label' => 'ส่ง swab', 'action' => 'save-swab', 'can' => true];
                }
            } elseif ($user->role_names->contains('md')) { // MD save to swab
                if ($visit->form['management']['np_swab'] && ! $visit->swabbed) {
                    $flash['action-menu'][] = ['icon' => 'share-square', 'label' => 'ส่ง swab', 'action' => 'save-swab', 'can' => true];
                    if ($visit->form['management']['container_no'] ?? false) {
                        $flash['action-menu'][] = ['icon' => 'share-square', 'label' => 'จำหน่ายได้ swab แล้ว', 'action' => 'save-discharge-swabbed', 'can' => true];
                    }
                }
            }
        }

        $this->manager->setFlash($flash);

        if ($visit->patient) {
            $visit->patient_document_id = $visit->patient->profile['document_id'];
            $visit->has_patient = true;
            $visit->is_female = ($visit->patient->profile['gender'] ?? '') === 'female';
            unset($visit->patient);
        } else {
            $visit->has_patient = false;
            $visit->is_female = true;
        }

        $configs = $this->manager->getConfigs($visit);
        $configs['can'] = $can;

        // history
        $records = Visit::wherePatientId($visit->patient_id)
                        ->whereStatus(4)
                        ->where('date_visit', '>=', $visit->date_visit->addDays(-90))
                        ->orderByDesc('date_visit')
                        ->get()
                        ->transform(fn ($v) => [
                            'date_visit' => $v->date_visit->format('d M Y'),
                            'result' => $v->atk_positive_case ? 'ATK positive' : $v->form['management']['np_swab_result'],
                            'screen_type' => $v->screen_type,
                            'risk' => $v->form['exposure']['evaluation'],
                            'ct' => $v->form['management']['np_swab_result_note'],
                            'swabbed' => $v->swabbed,
                            'slug' => $v->slug,
                            'comorbids' => $v->form['comorbids'],
                            'vaccination' => $v->form['vaccination'],
                            'note' => $v->form['note'],
                        ]);

        return Inertia::render('Visits/Edit', [
            'visit' => $visit,
            'formConfigs' => $configs,
            'records' => $records
        ]);
    }

    public function update(Visit $visit)
    {
        (new VisitManager())->saveVisit($visit, Request::all(), Auth::user());

        if ($visit->swabbed) {
            $visit->discharged_at = now();
            $form = $visit->form;
            $visit->enlisted_swab_at = $form['management']['original_enlisted_swab_at'];
            unset($form['management']['original_enlisted_swab_at']);
            $visit->status = 'discharged';
            $visit->save();
        }

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
                'topics' => ['ATK', 'ประวัติอื่น', 'ประวัติเสี่ยง', 'โรคประจำตัว', 'ประวัติการฉีดวัคซีน COVID-19', 'วินิจฉัย', 'การจัดการ', 'คำแนะนำสำหรับผู้ป่วย', 'note'],
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
        if ($visit->swabbed) {
            $visit->forceFill([
                'form->management->original_enlisted_swab_at' => $visit->enlisted_swab_at ? $visit->enlisted_swab_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            ]);
        }
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
        Cache::put('mr-list-new', time()); // fire event to mr event source
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
