<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Managers\PatientManager;
use App\Managers\VisitManager;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class WonderWomenController extends Controller
{
    public function __invoke()
    {
        Request::validate([
                'token' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if ($value !== config('app.ww_token')) {
                            $fail('The '.$attribute.' is invalid.');
                        }
                    },
                ],
                'hn' => 'required',
                'type' => 'required',
                'date' => 'required',
                'result' => 'required',
                'screenshot' => 'file',
        ]);

        if (Request::has('screenshot')) {
            $path = Request::file('screenshot')->store('public/croissant');
        } else {
            $path = null;
        }

        $patient = (new PatientManager)->manage(Request::input('hn'));
        if (! $patient['found']) {
            abort(422);
        }

        $visitDateStr = Carbon::createFromFormat('d-m-y', Request::input('date'))->format('Y-m-d');
        $visit = Visit::wherePatientId($patient['patient']->id)
                      ->whereDateVisit($visitDateStr)
                      ->first();

        if ($visit) {
            return [
                'ok' => true,
            ];
        }

        $visit = new Visit();
        $visit->slug = Str::uuid()->toString();
        $visit->date_visit = $visitDateStr;
        $visit->patient_id = $patient['patient']->id;
        $visit->patient_type = str_contains(Request::input('type'), 'SI') ? 'เจ้าหน้าที่ศิริราช' : 'บุคคลทั่วไป';
        $form = (new VisitManager)->initForm();
        $form['management']['np_swab_result'] = Request::input('result');
        if (Request::has('note')) {
            $form['management']['np_swab_result_note'] = Request::input('note');
        }
        $form['management']['screenshot'] = $path ? str_replace('public', 'storage', $path) : null;
        $visit->form = $form;
        $visit->status = 'screen';
        $visit->enlisted_screen_at = now();
        $visit->save();

        VisitUpdated::dispatch($visit);

        return ['ok' => true];
    }
}
