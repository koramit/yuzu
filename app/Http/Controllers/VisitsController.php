<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        // Request::session()->flash('main-menu-links', [
        //     ['icon' => 'clipboard-list', 'label' => 'รายการเคส', 'route' => 'refer-cases'],
        //     ['icon' => 'clipboard-list', 'label' => 'รายการเคส', 'route' => 'refer-cases'],
        // ]);

        Request::session()->flash('action-menu', [
            // ['icon' => 'clipboard-list', 'label' => 'รายการเคส', 'route' => 'refer-cases'],
            ['icon' => 'notes-medical', 'label' => 'เพิ่มเคสใหม่', 'action' => 'create-visit'],
        ]);

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

        $todayStr = now(Auth::user()->timezone)->format('Y-m-d');

        $form = (new VisitManager)->initForm();

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
                          ->where('form->patient_name', $data['patient_name'])
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
        $visit->creator_id = Auth::id();
        $visit->updater_id = Auth::id();
        $visit->save();

        return Redirect::route('visits.edit', $visit);
    }

    public function edit(Visit $visit)
    {
        Request::session()->flash('main-menu-links', [
            ['icon' => 'clipboard-list', 'label' => 'รายการเคส', 'route' => 'visits'],
            // ['icon' => 'clipboard-list', 'label' => 'รายการเคส', 'route' => 'refer-cases'],
        ]);

        $visit->load('patient');

        Request::session()->flash('page-title', $visit->patient_name.'@'.$visit->date_visit->format('d M Y'));

        if ($visit->patient) {
            $visit->patient_document_id = $visit->patient->profile['document_id'];
            $visit->has_patient = true;
            unset($visit->patient);
        } else {
            $visit->has_patient = false;
        }

        return Inertia::render('Visits/Edit', [
            'visit' => $visit,
            'formConfigs' => (new VisitManager)->getConfigs($visit),
            'configDates' => [
                'next_7_days' => now(Auth::user()->timezone)->addDays(7)->format('Y-m-d'),
                'next_14_days' => now(Auth::user()->timezone)->addDays(14)->format('Y-m-d'),
            ],
        ]);
    }

    public function update(Visit $visit)
    {
        $visit->forceFill(Request::all());
        $visit->save();

        return 'ok';
    }
}
