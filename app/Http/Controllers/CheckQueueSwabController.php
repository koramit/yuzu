<?php

namespace App\Http\Controllers;

use App\Managers\PatientManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class CheckQueueSwabController extends Controller
{
    public function create()
    {
        $result = Session::pull('check-queue-swab-result');

        return Inertia::render('CheckQueueSwab', [
            'date' => now()->tz('asia/bangkok')->format('d/m/Y'),
            'result' => [
                'hn' => $result['hn'] ?? null,
                'queue' => $result['queue'] ?? null,
            ]
        ]);
    }

    public function show()
    {
        Request::validate([
            'hn' => 'required|digits:8',
        ]);

        $patient = (new PatientManager)->manage(Request::input('hn'));

        if (! $patient['found']) {
            return back()->withErrors(['hn' => 'ไม่พบ HN นี้ในระบบ']);
        }

        $visit = Visit::wherePatientId($patient['patient']->id)
                    ->whereDateVisit(now()->tz('asia/bangkok')->format('Y-m-d'))
                    ->whereNotNull('form->management->specimen_no')
                    ->first();

        Session::put('check-queue-swab-result', [
            'hn' => Request::input('hn'),
            'queue' => !$visit ? 'ยังไม่ได้คิวสวอบ' : '#' . $visit->form['management']['specimen_no']
        ]);

        return Redirect::back();
    }
}
