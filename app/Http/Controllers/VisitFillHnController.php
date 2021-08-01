<?php

namespace App\Http\Controllers;

use App\Events\VisitUpdated;
use App\Managers\PatientManager;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class VisitFillHnController extends Controller
{
    public function __invoke(Visit $visit)
    {
        $patient = (new PatientManager())->manage(Request::input('hn'));
        $visit->patient_id = $patient['patient']->id;
        $visit->forceFill([
            'form->patient->hn' => $patient['patient']->hn,
            'form->patient->name' => $patient['patient']->full_name,
        ]);
        $visit->save();
        $visit->actions()->create([
            'action' => 'fill-hn',
            'visit_id' => $visit->id,
            'user_id' => Auth::id(),
        ]);
        VisitUpdated::dispatch($visit);

        return redirect()->back();
    }
}
