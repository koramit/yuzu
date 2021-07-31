<?php

namespace App\Http\Controllers;

use App\Managers\PatientManager;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class AppointmentsController extends Controller
{
    public function __invoke()
    {
        $data = Request::all();
        $user = Auth::user();
        // $todayStr = now($user->timezone)->format('Y-m-d');
        $form = (new VisitManager())->initForm();
        $patient = (new PatientManager())->manage($data['hn']);
        /* SHOULD BE FOUND */
        if (! $patient['found']) {
            return '🥺';
        }
        $visit = Visit::whereDateVisit($data['date_visit'])
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
        $visit->slug = Str::uuid()->toString();
        $visit->date_visit = $data['date_visit'];
        $visit->patient_type = 'เจ้าหน้าที่ศิริราช';
        $visit->form = $form;
        $visit->status = 'appointment';
        $visit->enlisted_screen_at = now();
        $visit->save();
        $visit->actions()->create(['action' => 'create', 'user_id' => $user->id]);

        return Redirect::route('visits.edit', $visit);
    }
}
